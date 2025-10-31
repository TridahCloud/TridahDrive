<?php

namespace App\Services;

use App\Models\RecurringTransaction;
use App\Models\BookTransaction;
use App\Models\Drive;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecurringTransactionService
{
    /**
     * Generate a transaction from a recurring transaction template
     *
     * @param RecurringTransaction $recurringTransaction
     * @param Carbon|null $transactionDate
     * @param array $overrides Override default values (amount, description, etc.)
     * @return BookTransaction
     */
    public function generateTransaction(
        RecurringTransaction $recurringTransaction,
        ?Carbon $transactionDate = null,
        array $overrides = []
    ): BookTransaction {
        $transactionDate = $transactionDate ?? $recurringTransaction->next_due_date;

        $transactionData = [
            'drive_id' => $recurringTransaction->drive_id,
            'date' => $transactionDate,
            'description' => $overrides['description'] ?? $recurringTransaction->description ?? $recurringTransaction->name,
            'amount' => $overrides['amount'] ?? $recurringTransaction->amount,
            'type' => $recurringTransaction->type,
            'account_id' => $recurringTransaction->account_id,
            'category_id' => $recurringTransaction->category_id,
            'payee' => $overrides['payee'] ?? $recurringTransaction->payee,
            'payment_method' => $overrides['payment_method'] ?? $recurringTransaction->payment_method,
            'reference' => $overrides['reference'] ?? "Recurring: {$recurringTransaction->name}",
            'status' => $overrides['status'] ?? 'pending',
            'notes' => $overrides['notes'] ?? $recurringTransaction->notes,
            'created_by' => Auth::id() ?? $recurringTransaction->created_by,
        ];

        return BookTransaction::create($transactionData);
    }

    /**
     * Create a transaction from recurring template and update next due date
     *
     * @param RecurringTransaction $recurringTransaction
     * @param Carbon|null $transactionDate
     * @param array $overrides
     * @return BookTransaction
     */
    public function createAndScheduleNext(
        RecurringTransaction $recurringTransaction,
        ?Carbon $transactionDate = null,
        array $overrides = []
    ): BookTransaction {
        DB::beginTransaction();
        
        try {
            // Generate the transaction
            $transaction = $this->generateTransaction($recurringTransaction, $transactionDate, $overrides);

            // Update next due date if still active and not past end date
            if ($recurringTransaction->is_active) {
                $newNextDueDate = $recurringTransaction->calculateNextDueDate($transactionDate);
                
                // Check if it's past the end date
                if ($recurringTransaction->end_date === null || $newNextDueDate <= $recurringTransaction->end_date) {
                    $recurringTransaction->update([
                        'next_due_date' => $newNextDueDate,
                    ]);
                } else {
                    // Deactivate if past end date
                    $recurringTransaction->update([
                        'is_active' => false,
                    ]);
                }
            }

            DB::commit();
            
            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Skip the next occurrence without creating a transaction
     *
     * @param RecurringTransaction $recurringTransaction
     * @return void
     */
    public function skipNext(RecurringTransaction $recurringTransaction): void
    {
        $newNextDueDate = $recurringTransaction->calculateNextDueDate();
        
        // Check if it's past the end date
        if ($recurringTransaction->end_date === null || $newNextDueDate <= $recurringTransaction->end_date) {
            $recurringTransaction->update([
                'next_due_date' => $newNextDueDate,
            ]);
        } else {
            // Deactivate if past end date
            $recurringTransaction->update([
                'is_active' => false,
            ]);
        }
    }

    /**
     * Get upcoming recurring transactions for a drive
     *
     * @param Drive $drive
     * @param int $days Number of days ahead to look
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUpcoming(Drive $drive, int $days = 30)
    {
        return $drive->recurringTransactions()
            ->with(['account', 'category'])
            ->upcoming($days)
            ->orderBy('next_due_date', 'asc')
            ->get();
    }

    /**
     * Get due/overdue recurring transactions for a drive
     *
     * @param Drive $drive
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDue(Drive $drive)
    {
        return $drive->recurringTransactions()
            ->with(['account', 'category'])
            ->due()
            ->orderBy('next_due_date', 'asc')
            ->get();
    }
}

