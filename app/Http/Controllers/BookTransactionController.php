<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Drive;
use App\Models\BookTransaction;
use App\Models\Account;
use App\Models\Category;
use App\Models\TransactionAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BookTransactionController extends Controller
{
    /**
     * Display the BookKeeper dashboard
     */
    public function dashboard(Drive $drive)
    {
        $this->authorize('view', $drive);

        // Get financial summary for current month
        $dateFrom = now()->startOfMonth();
        $dateTo = now()->endOfMonth();

        $stats = [
            'total_income' => $drive->bookTransactions()
                ->where('type', 'income')
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('status', '!=', 'pending')
                ->sum('amount'),
            'total_expense' => $drive->bookTransactions()
                ->where('type', 'expense')
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('status', '!=', 'pending')
                ->sum('amount'),
            'total_transactions' => $drive->bookTransactions()
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->count(),
            'pending_transactions' => $drive->bookTransactions()
                ->where('status', 'pending')
                ->count(),
            'cleared_transactions' => $drive->bookTransactions()
                ->where('status', 'cleared')
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->count(),
            'reconciled_transactions' => $drive->bookTransactions()
                ->where('status', 'reconciled')
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->count(),
        ];

        // Get recent transactions
        $recentTransactions = $drive->bookTransactions()
            ->with(['account', 'category', 'creator'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get top accounts by transaction count
        $topAccounts = $drive->accounts()
            ->withCount('transactions')
            ->orderBy('transactions_count', 'desc')
            ->limit(5)
            ->get();

        // Get top categories by transaction count
        $topCategories = $drive->categories()
            ->withCount('transactions')
            ->orderBy('transactions_count', 'desc')
            ->limit(5)
            ->get();

        // Calculate net income
        $stats['net_income'] = $stats['total_income'] - $stats['total_expense'];

        // Get upcoming recurring transactions (next 30 days)
        $recurringTransactionService = app(\App\Services\RecurringTransactionService::class);
        $upcomingRecurring = $recurringTransactionService->getUpcoming($drive, 30);
        $dueRecurring = $recurringTransactionService->getDue($drive);

        return view('bookkeeper.dashboard', compact(
            'drive',
            'stats',
            'recentTransactions',
            'topAccounts',
            'topCategories',
            'dateFrom',
            'dateTo',
            'upcomingRecurring',
            'dueRecurring'
        ));
    }

    /**
     * Display a listing of transactions for the drive
     */
    public function index(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);

        $query = $drive->bookTransactions()
            ->with(['account', 'category', 'creator']);

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Get stats
        $stats = [
            'total_income' => $drive->bookTransactions()
                ->where('type', 'income')
                ->where('status', '!=', 'pending')
                ->sum('amount'),
            'total_expense' => $drive->bookTransactions()
                ->where('type', 'expense')
                ->where('status', '!=', 'pending')
                ->sum('amount'),
            'total_pending' => $drive->bookTransactions()
                ->where('status', 'pending')
                ->count(),
        ];

        // Get filter options
        $accounts = $drive->accounts()->where('is_active', true)->orderBy('name')->get();
        $categories = $drive->categories()->where('is_active', true)->orderBy('name')->get();

        return view('bookkeeper.transactions.index', compact(
            'drive', 
            'transactions', 
            'stats', 
            'accounts', 
            'categories'
        ));
    }

    /**
     * Show the form for creating a new transaction
     */
    public function create(Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create transactions.');
        }

        $accounts = $drive->accounts()->where('is_active', true)->orderBy('name')->get();
        $categories = $drive->categories()->where('is_active', true)->orderBy('name')->get();

        return view('bookkeeper.transactions.create', compact('drive', 'accounts', 'categories'));
    }

    /**
     * Store a newly created transaction
     */
    public function store(StoreTransactionRequest $request, Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create transactions.');
        }

        $validated = $request->validated();

        // Verify account belongs to drive
        $account = Account::findOrFail($validated['account_id']);
        if ($account->drive_id !== $drive->id) {
            return redirect()->back()
                ->withErrors(['account_id' => 'Invalid account.'])
                ->withInput();
        }

        // Verify category belongs to drive if provided
        if (!empty($validated['category_id'])) {
            $category = Category::findOrFail($validated['category_id']);
            if ($category->drive_id !== $drive->id) {
                return redirect()->back()
                    ->withErrors(['category_id' => 'Invalid category.'])
                    ->withInput();
            }
        }

        $transaction = $drive->bookTransactions()->create(array_merge($validated, [
            'created_by' => Auth::id(),
            'status' => $validated['status'] ?? 'pending',
        ]));

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                try {
                    $this->storeAttachment($transaction, $file);
                } catch (\Exception $e) {
                    return redirect()->back()
                        ->withErrors(['attachments' => $e->getMessage()])
                        ->withInput();
                }
            }
        }

        return redirect()->route('drives.bookkeeper.transactions.show', [$drive, $transaction])
            ->with('success', 'Transaction created successfully!');
    }

    /**
     * Display the specified transaction
     */
    public function show(Drive $drive, BookTransaction $transaction)
    {
        $this->authorize('view', $drive);

        if ($transaction->drive_id !== $drive->id) {
            abort(404);
        }

        $transaction->load(['account', 'category', 'creator', 'attachments']);

        return view('bookkeeper.transactions.show', compact('drive', 'transaction'));
    }

    /**
     * Show the form for editing the specified transaction
     */
    public function edit(Drive $drive, BookTransaction $transaction)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit transactions.');
        }

        if ($transaction->drive_id !== $drive->id) {
            abort(404);
        }

        // Load attachments relationship
        $transaction->load('attachments');

        $accounts = $drive->accounts()->where('is_active', true)->orderBy('name')->get();
        $categories = $drive->categories()->where('is_active', true)->orderBy('name')->get();

        return view('bookkeeper.transactions.edit', compact('drive', 'transaction', 'accounts', 'categories'));
    }

    /**
     * Update the specified transaction
     */
    public function update(UpdateTransactionRequest $request, Drive $drive, BookTransaction $transaction)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit transactions.');
        }

        if ($transaction->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validated();

        // Verify account belongs to drive
        $account = Account::findOrFail($validated['account_id']);
        if ($account->drive_id !== $drive->id) {
            return redirect()->back()
                ->withErrors(['account_id' => 'Invalid account.'])
                ->withInput();
        }

        // Verify category belongs to drive if provided
        if (!empty($validated['category_id'])) {
            $category = Category::findOrFail($validated['category_id']);
            if ($category->drive_id !== $drive->id) {
                return redirect()->back()
                    ->withErrors(['category_id' => 'Invalid category.'])
                    ->withInput();
            }
        }

        $transaction->update($validated);
        
        // Refresh transaction to get updated data
        $transaction->refresh();

        // Handle new file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                try {
                    $this->storeAttachment($transaction, $file);
                } catch (\Exception $e) {
                    return redirect()->back()
                        ->withErrors(['attachments' => $e->getMessage()])
                        ->withInput();
                }
            }
        }

        return redirect()->route('drives.bookkeeper.transactions.show', [$drive, $transaction])
            ->with('success', 'Transaction updated successfully!');
    }

    /**
     * Remove the specified transaction
     */
    public function destroy(Drive $drive, BookTransaction $transaction)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to delete
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot delete transactions.');
        }

        if ($transaction->drive_id !== $drive->id) {
            abort(404);
        }

        $transaction->delete();

        return redirect()->route('drives.bookkeeper.transactions.index', $drive)
            ->with('success', 'Transaction deleted successfully!');
    }

    /**
     * Store an attachment for a transaction
     */
    protected function storeAttachment(BookTransaction $transaction, $file)
    {
        // Validate file size (10MB = 10240 KB)
        $maxSize = 10 * 1024 * 1024; // 10MB in bytes
        if ($file->getSize() > $maxSize) {
            throw new \Exception('File size exceeds the maximum allowed size of 10MB.');
        }

        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = 'transaction-attachments/' . $transaction->drive_id . '/' . $transaction->id;
        
        // Store file
        $filePath = $file->storeAs($path, $filename, 'public');

        // Create attachment record
        TransactionAttachment::create([
            'transaction_id' => $transaction->id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => Auth::id(),
        ]);
    }

    /**
     * Serve/download an attachment
     */
    public function showAttachment(Drive $drive, BookTransaction $transaction, TransactionAttachment $attachment)
    {
        $this->authorize('view', $drive);

        if ($transaction->drive_id !== $drive->id || $attachment->transaction_id !== $transaction->id) {
            abort(404);
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->response($attachment->file_path, $attachment->original_filename);
    }

    /**
     * Delete an attachment
     */
    public function destroyAttachment(Drive $drive, BookTransaction $transaction, TransactionAttachment $attachment)
    {
        $this->authorize('view', $drive);

        if ($transaction->drive_id !== $drive->id || $attachment->transaction_id !== $transaction->id) {
            abort(404);
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        // Delete attachment record
        $attachment->delete();

        return redirect()->route('drives.bookkeeper.transactions.show', [$drive, $transaction])
            ->with('success', 'Attachment deleted successfully!');
    }

    /**
     * Generate a tax report for CPA
     */
    public function taxReport(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);

        // Get date range from request or default to current year
        $dateFrom = $request->filled('date_from') 
            ? \Carbon\Carbon::parse($request->date_from)->startOfDay()
            : now()->startOfYear();
        
        $dateTo = $request->filled('date_to')
            ? \Carbon\Carbon::parse($request->date_to)->endOfDay()
            : now()->endOfYear();

        // Get all transactions in date range (excluding pending transactions)
        $query = $drive->bookTransactions()
            ->with(['account', 'category'])
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->where('status', '!=', 'pending');

        // Apply additional filters
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $transactions = $query->orderBy('date', 'asc')->get();

        // Aggregate income by category
        $incomeByCategory = [];
        $incomeTotal = 0;

        // Aggregate expenses by category
        $expensesByCategory = [];
        $expensesTotal = 0;

        // Aggregate by account type
        $incomeByAccountType = [];
        $expensesByAccountType = [];

        // Detailed transaction list
        $incomeTransactions = [];
        $expenseTransactions = [];

        foreach ($transactions as $transaction) {
            $amount = (float) $transaction->amount;

            if ($transaction->type === 'income') {
                $incomeTotal += $amount;
                $categoryName = $transaction->category ? $transaction->category->name : 'Uncategorized';
                
                if (!isset($incomeByCategory[$categoryName])) {
                    $incomeByCategory[$categoryName] = [
                        'category' => $categoryName,
                        'count' => 0,
                        'total' => 0,
                        'transactions' => []
                    ];
                }
                
                $incomeByCategory[$categoryName]['count']++;
                $incomeByCategory[$categoryName]['total'] += $amount;
                $incomeByCategory[$categoryName]['transactions'][] = $transaction;

                // Group by account type
                $accountType = $transaction->account ? $transaction->account->type : 'unknown';
                if (!isset($incomeByAccountType[$accountType])) {
                    $incomeByAccountType[$accountType] = 0;
                }
                $incomeByAccountType[$accountType] += $amount;

                $incomeTransactions[] = $transaction;

            } elseif ($transaction->type === 'expense') {
                $expensesTotal += $amount;
                $categoryName = $transaction->category ? $transaction->category->name : 'Uncategorized';
                
                if (!isset($expensesByCategory[$categoryName])) {
                    $expensesByCategory[$categoryName] = [
                        'category' => $categoryName,
                        'count' => 0,
                        'total' => 0,
                        'transactions' => []
                    ];
                }
                
                $expensesByCategory[$categoryName]['count']++;
                $expensesByCategory[$categoryName]['total'] += $amount;
                $expensesByCategory[$categoryName]['transactions'][] = $transaction;

                // Group by account type
                $accountType = $transaction->account ? $transaction->account->type : 'unknown';
                if (!isset($expensesByAccountType[$accountType])) {
                    $expensesByAccountType[$accountType] = 0;
                }
                $expensesByAccountType[$accountType] += $amount;

                $expenseTransactions[] = $transaction;
            }
        }

        // Sort categories by total (descending)
        uasort($incomeByCategory, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        uasort($expensesByCategory, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        // Calculate net income
        $netIncome = $incomeTotal - $expensesTotal;

        // Get filter options for the form
        $accounts = $drive->accounts()->where('is_active', true)->orderBy('name')->get();
        $categories = $drive->categories()->where('is_active', true)->orderBy('name')->get();

        return view('bookkeeper.reports.tax-report', compact(
            'drive',
            'transactions',
            'incomeByCategory',
            'expensesByCategory',
            'incomeByAccountType',
            'expensesByAccountType',
            'incomeTransactions',
            'expenseTransactions',
            'incomeTotal',
            'expensesTotal',
            'netIncome',
            'dateFrom',
            'dateTo',
            'accounts',
            'categories'
        ));
    }
}
