<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BookTransaction;
use App\Models\Category;
use App\Models\Drive;
use App\Models\PayrollEntry;
use Illuminate\Support\Facades\Log;

class PayrollBookKeeperSyncService
{
    /**
     * Sync a payroll entry with BookKeeper transactions
     */
    public function syncPayrollEntry(PayrollEntry $payrollEntry): ?BookTransaction
    {
        $drive = $payrollEntry->drive;

        // Only sync paid payroll entries - if not paid, remove any existing transaction
        if ($payrollEntry->status !== 'paid') {
            // If there's an existing transaction, delete it (payroll is no longer paid)
            $this->removePayrollTransaction($payrollEntry);
            // Return null to indicate sync was not performed (entry not paid)
            return null;
        }

        // Check if transaction already exists for this payroll entry
        $existingTransaction = BookTransaction::where('drive_id', $drive->id)
            ->where('reference', 'PAYROLL-' . $payrollEntry->id)
            ->first();

        if ($existingTransaction) {
            // Update existing transaction
            return $this->updatePayrollTransaction($payrollEntry, $existingTransaction);
        }

        // Create new transaction for paid payroll
        return $this->createPayrollTransaction($payrollEntry);
    }

    /**
     * Map payroll payment method to BookKeeper payment method
     */
    protected function mapPaymentMethod(?string $payrollMethod): ?string
    {
        if (!$payrollMethod) {
            return null;
        }

        // Map payroll payment methods to BookKeeper payment methods
        $mapping = [
            'direct_deposit' => 'bank_transfer',
            'check' => 'check',
            'cash' => 'cash',
            'other' => 'other',
        ];

        return $mapping[$payrollMethod] ?? 'other';
    }

    /**
     * Create a BookKeeper transaction for a paid payroll entry
     */
    protected function createPayrollTransaction(PayrollEntry $payrollEntry): BookTransaction
    {
        $drive = $payrollEntry->drive;
        $person = $payrollEntry->person;
        
        // Get or create expense account for payroll
        $payrollAccount = $this->getOrCreatePayrollAccount($drive, auth()->id() ?? 1);
        
        // Get or create payroll category
        $payrollCategory = $this->getOrCreatePayrollCategory($drive, auth()->id() ?? 1);

        $transaction = BookTransaction::create([
            'drive_id' => $drive->id,
            'date' => $payrollEntry->pay_date,
            'description' => 'Payroll: ' . $payrollEntry->payroll_period . ' - ' . $person->full_name,
            'reference' => 'PAYROLL-' . $payrollEntry->id,
            'amount' => $payrollEntry->net_pay, // Net pay is the expense
            'type' => 'expense',
            'account_id' => $payrollAccount->id,
            'category_id' => $payrollCategory->id,
            'payee' => $person->full_name,
            'payment_method' => $this->mapPaymentMethod($payrollEntry->payment_method),
            'status' => 'cleared', // Payroll paid = cleared transaction
            'notes' => "Period: {$payrollEntry->payroll_period}\n" .
                      "Gross Pay: $" . number_format($payrollEntry->gross_pay, 2) . "\n" .
                      "Deductions: $" . number_format($payrollEntry->total_deductions, 2) . "\n" .
                      "Net Pay: $" . number_format($payrollEntry->net_pay, 2),
            'created_by' => auth()->id() ?? 1,
        ]);

        // Mark payroll entry as synced
        $payrollEntry->update([
            'book_transaction_id' => $transaction->id,
            'synced_to_bookkeeper' => true,
            'synced_at' => now(),
        ]);

        return $transaction;
    }

    /**
     * Update an existing transaction for a payroll entry
     */
    protected function updatePayrollTransaction(PayrollEntry $payrollEntry, BookTransaction $transaction): BookTransaction
    {
        $person = $payrollEntry->person;
        $payrollAccount = $this->getOrCreatePayrollAccount($payrollEntry->drive, auth()->id() ?? 1);
        $payrollCategory = $this->getOrCreatePayrollCategory($payrollEntry->drive, auth()->id() ?? 1);

        $transaction->update([
            'date' => $payrollEntry->pay_date,
            'description' => 'Payroll: ' . $payrollEntry->payroll_period . ' - ' . $person->full_name,
            'amount' => $payrollEntry->net_pay,
            'account_id' => $payrollAccount->id,
            'category_id' => $payrollCategory->id,
            'payee' => $person->full_name,
            'payment_method' => $this->mapPaymentMethod($payrollEntry->payment_method),
            'status' => 'cleared',
            'notes' => "Period: {$payrollEntry->payroll_period}\n" .
                      "Gross Pay: $" . number_format($payrollEntry->gross_pay, 2) . "\n" .
                      "Deductions: $" . number_format($payrollEntry->total_deductions, 2) . "\n" .
                      "Net Pay: $" . number_format($payrollEntry->net_pay, 2),
        ]);

        // Update sync status
        $payrollEntry->update([
            'book_transaction_id' => $transaction->id,
            'synced_to_bookkeeper' => true,
            'synced_at' => now(),
        ]);

        return $transaction;
    }

    /**
     * Remove transaction when payroll is no longer paid
     */
    protected function removePayrollTransaction(PayrollEntry $payrollEntry): void
    {
        $transaction = BookTransaction::where('drive_id', $payrollEntry->drive_id)
            ->where('reference', 'PAYROLL-' . $payrollEntry->id)
            ->first();

        if ($transaction) {
            $transaction->delete();
        }

        // Update sync status
        $payrollEntry->update([
            'book_transaction_id' => null,
            'synced_to_bookkeeper' => false,
            'synced_at' => null,
        ]);
    }

    /**
     * Get or create a payroll expense account for the drive
     */
    protected function getOrCreatePayrollAccount(Drive $drive, int $userId): Account
    {
        // Try to find existing "Payroll Expense" or "Salary Expense" account
        $account = Account::where('drive_id', $drive->id)
            ->where('type', 'expense')
            ->where(function ($query) {
                $query->where('name', 'Payroll Expense')
                    ->orWhere('name', 'Salary Expense')
                    ->orWhere('name', 'Wages Expense');
            })
            ->where('is_active', true)
            ->first();

        if (!$account) {
            // Check if there's a parent "Expense" account
            $expenseParent = Account::where('drive_id', $drive->id)
                ->where('type', 'expense')
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->first();

            // Create "Payroll Expense" account
            $account = Account::create([
                'drive_id' => $drive->id,
                'parent_id' => $expenseParent?->id,
                'account_code' => $this->generatePayrollAccountCode($drive, 'expense'),
                'name' => 'Payroll Expense',
                'type' => 'expense',
                'description' => 'Payroll and salary expenses',
                'is_active' => true,
                'is_system' => false,
                'created_by' => $userId,
            ]);
        }

        return $account;
    }

    /**
     * Get or create a payroll category for the drive
     */
    protected function getOrCreatePayrollCategory(Drive $drive, int $userId): Category
    {
        // Try to find existing "Payroll" category
        $category = Category::where('drive_id', $drive->id)
            ->where(function ($query) {
                $query->where('name', 'Payroll')
                    ->orWhere('name', 'Payroll Expenses')
                    ->orWhere('name', 'Salary')
                    ->orWhere('name', 'Wages');
            })
            ->where('is_active', true)
            ->first();

        if (!$category) {
            // Create "Payroll" category
            $category = Category::create([
                'drive_id' => $drive->id,
                'parent_id' => null, // Top-level category
                'name' => 'Payroll',
                'description' => 'Payroll and salary expenses',
                'color' => '#FF6B6B', // Red color for expenses
                'is_active' => true,
                'is_system' => false,
                'created_by' => $userId,
            ]);
        }

        return $category;
    }

    /**
     * Generate a unique account code for payroll expense
     */
    protected function generatePayrollAccountCode(Drive $drive, string $type): string
    {
        // Use a standard expense account code for payroll
        $baseCode = '7200'; // Payroll expense account code
        
        // Check if this code already exists in the drive
        $exists = Account::where('drive_id', $drive->id)
            ->where('account_code', $baseCode)
            ->exists();
        
        if ($exists) {
            // Find the highest expense account code
            $maxCode = Account::where('drive_id', $drive->id)
                ->where('type', 'expense')
                ->whereNotNull('account_code')
                ->get()
                ->map(function ($account) {
                    // Extract numeric part
                    if (preg_match('/^(\d+)/', $account->account_code, $matches)) {
                        return (int) $matches[1];
                    }
                    return 0;
                })
                ->max() ?? 7199;
            
            // Generate next available code (7200, 7300, 7400, etc.)
            $nextCode = (int) floor(($maxCode + 100) / 100) * 100;
            if ($nextCode < 7200) {
                $nextCode = 7200;
            }
            
            // Ensure uniqueness
            while (Account::where('drive_id', $drive->id)
                ->where('account_code', (string) $nextCode)
                ->exists()) {
                $nextCode += 100;
            }
            
            return (string) $nextCode;
        }
        
        return $baseCode;
    }

    /**
     * Sync all paid payroll entries for a drive (useful for initial setup)
     */
    public function syncAllPaidPayroll(Drive $drive): int
    {
        $paidPayroll = $drive->payrollEntries()
            ->where('status', 'paid')
            ->where('synced_to_bookkeeper', false)
            ->get();

        $synced = 0;
        foreach ($paidPayroll as $payrollEntry) {
            try {
                $this->syncPayrollEntry($payrollEntry);
                $synced++;
            } catch (\Exception $e) {
                Log::error("Failed to sync payroll entry {$payrollEntry->id}: " . $e->getMessage());
            }
        }

        return $synced;
    }
}
