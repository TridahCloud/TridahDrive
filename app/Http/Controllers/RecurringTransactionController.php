<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\RecurringTransaction;
use App\Models\Account;
use App\Models\Category;
use App\Services\RecurringTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecurringTransactionController extends Controller
{
    protected $recurringTransactionService;

    public function __construct(RecurringTransactionService $recurringTransactionService)
    {
        $this->recurringTransactionService = $recurringTransactionService;
    }

    /**
     * Display a listing of recurring transactions
     */
    public function index(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);

        $query = $drive->recurringTransactions()
            ->with(['account', 'category', 'creator']);

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        if ($request->filled('frequency')) {
            $query->where('frequency', $request->frequency);
        }

        $recurringTransactions = $query->orderBy('next_due_date', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $accounts = $drive->accounts()->where('is_active', true)->orderBy('name')->get();
        $categories = $drive->categories()->where('is_active', true)->orderBy('name')->get();

        return view('bookkeeper.recurring-transactions.index', compact(
            'drive',
            'recurringTransactions',
            'accounts',
            'categories'
        ));
    }

    /**
     * Show upcoming/due recurring transactions
     */
    public function upcoming(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);

        $days = $request->filled('days') ? (int) $request->days : 30;

        $upcoming = $this->recurringTransactionService->getUpcoming($drive, $days);
        $due = $this->recurringTransactionService->getDue($drive);

        return view('bookkeeper.recurring-transactions.upcoming', compact(
            'drive',
            'upcoming',
            'due',
            'days'
        ));
    }

    /**
     * Show the form for creating a new recurring transaction
     */
    public function create(Drive $drive)
    {
        $this->authorize('view', $drive);

        $accounts = $drive->accounts()->where('is_active', true)->orderBy('name')->get();
        $categories = $drive->categories()->where('is_active', true)->orderBy('name')->get();

        return view('bookkeeper.recurring-transactions.create', compact('drive', 'accounts', 'categories'));
    }

    /**
     * Store a newly created recurring transaction
     */
    public function store(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:income,expense',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'payee' => 'nullable|string|max:255',
            'payment_method' => 'nullable|in:cash,check,credit_card,debit_card,bank_transfer,other',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

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

        // Calculate next due date from start date
        $startDate = Carbon::parse($validated['start_date']);
        $nextDueDate = match($validated['frequency']) {
            'daily' => $startDate->copy()->addDay(),
            'weekly' => $startDate->copy()->addWeek(),
            'monthly' => $startDate->copy()->addMonth(),
            'yearly' => $startDate->copy()->addYear(),
            default => $startDate->copy()->addMonth(),
        };

        $recurringTransaction = $drive->recurringTransactions()->create(array_merge($validated, [
            'created_by' => Auth::id(),
            'next_due_date' => $nextDueDate,
            'is_active' => $validated['is_active'] ?? true,
        ]));

        return redirect()->route('drives.bookkeeper.recurring-transactions.show', [$drive, $recurringTransaction])
            ->with('success', 'Recurring transaction created successfully!');
    }

    /**
     * Display the specified recurring transaction
     */
    public function show(Drive $drive, RecurringTransaction $recurringTransaction)
    {
        $this->authorize('view', $drive);

        if ($recurringTransaction->drive_id !== $drive->id) {
            abort(404);
        }

        $recurringTransaction->load(['account', 'category', 'creator']);

        return view('bookkeeper.recurring-transactions.show', compact('drive', 'recurringTransaction'));
    }

    /**
     * Show the form for editing the specified recurring transaction
     */
    public function edit(Drive $drive, RecurringTransaction $recurringTransaction)
    {
        $this->authorize('view', $drive);

        if ($recurringTransaction->drive_id !== $drive->id) {
            abort(404);
        }

        $accounts = $drive->accounts()->where('is_active', true)->orderBy('name')->get();
        $categories = $drive->categories()->where('is_active', true)->orderBy('name')->get();

        return view('bookkeeper.recurring-transactions.edit', compact('drive', 'recurringTransaction', 'accounts', 'categories'));
    }

    /**
     * Update the specified recurring transaction
     */
    public function update(Request $request, Drive $drive, RecurringTransaction $recurringTransaction)
    {
        $this->authorize('view', $drive);

        if ($recurringTransaction->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:income,expense',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'next_due_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'payee' => 'nullable|string|max:255',
            'payment_method' => 'nullable|in:cash,check,credit_card,debit_card,bank_transfer,other',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

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

        $recurringTransaction->update(array_merge($validated, [
            'is_active' => $validated['is_active'] ?? $recurringTransaction->is_active,
        ]));

        return redirect()->route('drives.bookkeeper.recurring-transactions.show', [$drive, $recurringTransaction])
            ->with('success', 'Recurring transaction updated successfully!');
    }

    /**
     * Remove the specified recurring transaction
     */
    public function destroy(Drive $drive, RecurringTransaction $recurringTransaction)
    {
        $this->authorize('view', $drive);

        if ($recurringTransaction->drive_id !== $drive->id) {
            abort(404);
        }

        $recurringTransaction->delete();

        return redirect()->route('drives.bookkeeper.recurring-transactions.index', $drive)
            ->with('success', 'Recurring transaction deleted successfully!');
    }

    /**
     * Generate a transaction from a recurring transaction template
     */
    public function generate(Request $request, Drive $drive, RecurringTransaction $recurringTransaction)
    {
        $this->authorize('view', $drive);

        if ($recurringTransaction->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'transaction_date' => 'nullable|date',
            'amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'status' => 'nullable|in:pending,cleared,reconciled',
        ]);

        try {
            $transactionDate = $validated['transaction_date'] 
                ? Carbon::parse($validated['transaction_date'])
                : null;

            $overrides = array_filter([
                'amount' => $validated['amount'] ?? null,
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? null,
            ]);

            $transaction = $this->recurringTransactionService->createAndScheduleNext(
                $recurringTransaction,
                $transactionDate,
                $overrides
            );

            return redirect()->route('drives.bookkeeper.transactions.show', [$drive, $transaction])
                ->with('success', 'Transaction created successfully from recurring template!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to generate transaction: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Skip the next occurrence of a recurring transaction
     */
    public function skip(Drive $drive, RecurringTransaction $recurringTransaction)
    {
        $this->authorize('view', $drive);

        if ($recurringTransaction->drive_id !== $drive->id) {
            abort(404);
        }

        try {
            $this->recurringTransactionService->skipNext($recurringTransaction);

            return redirect()->back()
                ->with('success', 'Next occurrence skipped successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to skip occurrence: ' . $e->getMessage()]);
        }
    }
}
