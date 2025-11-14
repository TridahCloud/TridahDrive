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
        
        // Check if user has permission to view BookKeeper
        if (!$drive->userCanViewBookKeeper(auth()->user())) {
            abort(403, 'You do not have permission to access BookKeeper.');
        }

        // Get financial summary for current month
        $dateFrom = now()->startOfMonth();
        $dateTo = now()->endOfMonth();

        // Use aggregate methods if this is a parent drive (include sub-drives)
        if (!$drive->isSubDrive()) {
            $baseQuery = $drive->getTransactionsIncludingSubDrives();
        } else {
            $baseQuery = $drive->bookTransactions();
        }

        $stats = [
            'total_income' => (clone $baseQuery)
                ->where('type', 'income')
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('status', '!=', 'pending')
                ->sum('amount'),
            'total_expense' => (clone $baseQuery)
                ->where('type', 'expense')
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('status', '!=', 'pending')
                ->sum('amount'),
            'total_transactions' => (clone $baseQuery)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->count(),
            'pending_transactions' => (clone $baseQuery)
                ->where('status', 'pending')
                ->count(),
            'cleared_transactions' => (clone $baseQuery)
                ->where('status', 'cleared')
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->count(),
            'reconciled_transactions' => (clone $baseQuery)
                ->where('status', 'reconciled')
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->count(),
        ];

        // Get recent transactions
        $recentTransactions = (clone $baseQuery)
            ->with(['account', 'category', 'budget', 'creator', 'drive'])
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

        // Use aggregate methods if this is a parent drive (include sub-drives)
        if (!$drive->isSubDrive()) {
            $query = $drive->getTransactionsIncludingSubDrives()
                ->with(['account', 'category', 'budget', 'creator', 'drive']);
        } else {
            $query = $drive->bookTransactions()
                ->with(['account', 'category', 'budget', 'creator', 'drive']);
        }

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

        if ($request->filled('budget_id')) {
            $query->where('budget_id', $request->budget_id);
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
        // Use aggregate methods if this is a parent drive
        if (!$drive->isSubDrive()) {
            $baseStatsQuery = $drive->getTransactionsIncludingSubDrives();
        } else {
            $baseStatsQuery = $drive->bookTransactions();
        }

        $stats = [
            'total_income' => (clone $baseStatsQuery)
                ->where('type', 'income')
                ->where('status', '!=', 'pending')
                ->sum('amount'),
            'total_expense' => (clone $baseStatsQuery)
                ->where('type', 'expense')
                ->where('status', '!=', 'pending')
                ->sum('amount'),
            'total_pending' => (clone $baseStatsQuery)
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
        $budgets = $drive->budgets()->where('is_active', true)->orderBy('name')->get();

        // Check if budget_id is passed in query string
        $selectedBudgetId = request('budget_id');

        return view('bookkeeper.transactions.create', compact('drive', 'accounts', 'categories', 'budgets', 'selectedBudgetId'));
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

        // Convert date from user timezone to drive timezone
        $driveTimezone = $drive->getEffectiveTimezone();
        $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
        
        if (isset($validated['date'])) {
            $date = \Carbon\Carbon::parse($validated['date'], $userTimezone);
            $date->setTimezone($driveTimezone);
            $validated['date'] = $date->format('Y-m-d');
        }

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

        // Verify budget belongs to drive if provided
        if (!empty($validated['budget_id'])) {
            $budget = \App\Models\Budget::findOrFail($validated['budget_id']);
            if ($budget->drive_id !== $drive->id) {
                return redirect()->back()
                    ->withErrors(['budget_id' => 'Invalid budget.'])
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

        // Allow if transaction is from this drive or from one of its sub-drives
        $transactionDriveId = $transaction->drive_id;
        if ($transactionDriveId !== $drive->id) {
            // Check if transaction is from a sub-drive
            if (!$drive->isSubDrive()) {
                $driveIds = $drive->getDriveIdsIncludingSubDrives();
                if (!in_array($transactionDriveId, $driveIds)) {
                    abort(404);
                }
            } else {
                abort(404);
            }
        }

        $transaction->load(['account', 'category', 'budget', 'creator', 'attachments', 'drive']);

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

        // Allow if transaction is from this drive or from one of its sub-drives
        $transactionDriveId = $transaction->drive_id;
        if ($transactionDriveId !== $drive->id) {
            // Check if transaction is from a sub-drive
            if (!$drive->isSubDrive()) {
                $driveIds = $drive->getDriveIdsIncludingSubDrives();
                if (!in_array($transactionDriveId, $driveIds)) {
                    abort(404);
                }
            } else {
                abort(404);
            }
        }

        // Load attachments relationship
        $transaction->load('attachments');

        $accounts = $drive->accounts()->where('is_active', true)->orderBy('name')->get();
        $categories = $drive->categories()->where('is_active', true)->orderBy('name')->get();
        $budgets = $drive->budgets()->where('is_active', true)->orderBy('name')->get();

        return view('bookkeeper.transactions.edit', compact('drive', 'transaction', 'accounts', 'categories', 'budgets'));
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

        // Allow if transaction is from this drive or from one of its sub-drives
        $transactionDriveId = $transaction->drive_id;
        if ($transactionDriveId !== $drive->id) {
            // Check if transaction is from a sub-drive
            if (!$drive->isSubDrive()) {
                $driveIds = $drive->getDriveIdsIncludingSubDrives();
                if (!in_array($transactionDriveId, $driveIds)) {
                    abort(404);
                }
            } else {
                abort(404);
            }
        }

        $validated = $request->validated();

        // Convert date from user timezone to drive timezone
        $driveTimezone = $drive->getEffectiveTimezone();
        $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
        
        if (isset($validated['date'])) {
            $date = \Carbon\Carbon::parse($validated['date'], $userTimezone);
            $date->setTimezone($driveTimezone);
            $validated['date'] = $date->format('Y-m-d');
        }

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

        // Verify budget belongs to drive if provided
        if (!empty($validated['budget_id'])) {
            $budget = \App\Models\Budget::findOrFail($validated['budget_id']);
            if ($budget->drive_id !== $drive->id) {
                return redirect()->back()
                    ->withErrors(['budget_id' => 'Invalid budget.'])
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

        // Allow if transaction is from this drive or from one of its sub-drives
        $transactionDriveId = $transaction->drive_id;
        if ($transactionDriveId !== $drive->id) {
            // Check if transaction is from a sub-drive
            if (!$drive->isSubDrive()) {
                $driveIds = $drive->getDriveIdsIncludingSubDrives();
                if (!in_array($transactionDriveId, $driveIds)) {
                    abort(404);
                }
            } else {
                abort(404);
            }
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

        // Check attachment belongs to transaction
        if ($attachment->transaction_id !== $transaction->id) {
            abort(404);
        }

        // Allow if transaction is from this drive or from one of its sub-drives
        $transactionDriveId = $transaction->drive_id;
        if ($transactionDriveId !== $drive->id) {
            if (!$drive->isSubDrive()) {
                $driveIds = $drive->getDriveIdsIncludingSubDrives();
                if (!in_array($transactionDriveId, $driveIds)) {
                    abort(404);
                }
            } else {
                abort(404);
            }
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
        
        // Check attachment belongs to transaction
        if ($attachment->transaction_id !== $transaction->id) {
            abort(404);
        }

        // Allow if transaction is from this drive or from one of its sub-drives
        $transactionDriveId = $transaction->drive_id;
        if ($transactionDriveId !== $drive->id) {
            if (!$drive->isSubDrive()) {
                $driveIds = $drive->getDriveIdsIncludingSubDrives();
                if (!in_array($transactionDriveId, $driveIds)) {
                    abort(404);
                }
            } else {
                abort(404);
            }
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
        // Use aggregate methods if this is a parent drive (include sub-drives)
        if (!$drive->isSubDrive()) {
            $query = $drive->getTransactionsIncludingSubDrives()
                ->with(['account', 'category', 'budget', 'drive'])
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('status', '!=', 'pending');
        } else {
            $query = $drive->bookTransactions()
                ->with(['account', 'category', 'budget', 'drive'])
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('status', '!=', 'pending');
        }

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
