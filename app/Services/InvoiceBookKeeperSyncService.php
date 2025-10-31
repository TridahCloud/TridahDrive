<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BookTransaction;
use App\Models\Drive;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

class InvoiceBookKeeperSyncService
{
    /**
     * Sync an invoice with BookKeeper transactions
     */
    public function syncInvoice(Invoice $invoice): ?BookTransaction
    {
        $drive = $invoice->drive;

        // Only sync paid invoices
        if ($invoice->status !== 'paid') {
            // If there's an existing transaction, delete it (invoice is no longer paid)
            $this->removeInvoiceTransaction($invoice);
            return null;
        }

        // Check if transaction already exists for this invoice
        $existingTransaction = BookTransaction::where('drive_id', $drive->id)
            ->where('reference', $invoice->invoice_number)
            ->first();

        if ($existingTransaction) {
            // Update existing transaction
            return $this->updateInvoiceTransaction($invoice, $existingTransaction);
        }

        // Create new transaction for paid invoice
        return $this->createInvoiceTransaction($invoice);
    }

    /**
     * Create a BookKeeper transaction for a paid invoice
     */
    protected function createInvoiceTransaction(Invoice $invoice): BookTransaction
    {
        $drive = $invoice->drive;
        
        // Get or create revenue account for this drive
        $revenueAccount = $this->getOrCreateRevenueAccount($drive, $invoice->user_id);

        $transaction = BookTransaction::create([
            'drive_id' => $drive->id,
            'date' => $invoice->issue_date,
            'description' => 'Invoice Payment: ' . $invoice->invoice_number . ' - ' . ($invoice->client_name ?? 'Unknown Client'),
            'reference' => $invoice->invoice_number,
            'amount' => $invoice->total,
            'type' => 'income',
            'account_id' => $revenueAccount->id,
            'category_id' => null, // Could link to a category if needed
            'payee' => $invoice->client_name,
            'payment_method' => null,
            'status' => 'cleared', // Invoice paid = cleared transaction
            'notes' => $invoice->project ? "Project: {$invoice->project}" : null,
            'created_by' => $invoice->user_id,
        ]);

        return $transaction;
    }

    /**
     * Update an existing transaction for an invoice
     */
    protected function updateInvoiceTransaction(Invoice $invoice, BookTransaction $transaction): BookTransaction
    {
        $revenueAccount = $this->getOrCreateRevenueAccount($invoice->drive, $invoice->user_id);

        $transaction->update([
            'date' => $invoice->issue_date,
            'description' => 'Invoice Payment: ' . $invoice->invoice_number . ' - ' . ($invoice->client_name ?? 'Unknown Client'),
            'amount' => $invoice->total,
            'account_id' => $revenueAccount->id,
            'payee' => $invoice->client_name,
            'status' => 'cleared',
            'notes' => $invoice->project ? "Project: {$invoice->project}" : null,
        ]);

        return $transaction;
    }

    /**
     * Remove transaction when invoice is no longer paid
     */
    protected function removeInvoiceTransaction(Invoice $invoice): void
    {
        $transaction = BookTransaction::where('drive_id', $invoice->drive_id)
            ->where('reference', $invoice->invoice_number)
            ->first();

        if ($transaction) {
            $transaction->delete();
        }
    }

    /**
     * Get or create a revenue account for the drive
     */
    protected function getOrCreateRevenueAccount(Drive $drive, int $userId): Account
    {
        // Try to find existing "Sales Revenue" or "Service Revenue" account
        $account = Account::where('drive_id', $drive->id)
            ->where('type', 'revenue')
            ->where(function ($query) {
                $query->where('name', 'Sales Revenue')
                    ->orWhere('name', 'Service Revenue')
                    ->orWhere('name', 'Invoice Revenue');
            })
            ->where('is_active', true)
            ->first();

        if (!$account) {
            // Check if there's a parent "Revenue" account
            $revenueParent = Account::where('drive_id', $drive->id)
                ->where('type', 'revenue')
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->first();

            // Create "Invoice Revenue" account
            $account = Account::create([
                'drive_id' => $drive->id,
                'parent_id' => $revenueParent?->id,
                'account_code' => $this->generateAccountCode($drive, 'revenue'),
                'name' => 'Invoice Revenue',
                'type' => 'revenue',
                'description' => 'Revenue from paid invoices',
                'is_active' => true,
                'is_system' => false,
                'created_by' => $userId,
            ]);
        }

        return $account;
    }

    /**
     * Generate a unique account code for the drive
     */
    protected function generateAccountCode(Drive $drive, string $type): string
    {
        // Use a standard revenue account code
        $baseCode = '4100'; // Revenue account code
        
        // Check if this code already exists in the drive
        $exists = Account::where('drive_id', $drive->id)
            ->where('account_code', $baseCode)
            ->exists();
        
        if ($exists) {
            // Find the highest revenue account code
            $maxCode = Account::where('drive_id', $drive->id)
                ->where('type', 'revenue')
                ->whereNotNull('account_code')
                ->get()
                ->map(function ($account) {
                    // Extract numeric part
                    if (preg_match('/^(\d+)/', $account->account_code, $matches)) {
                        return (int) $matches[1];
                    }
                    return 0;
                })
                ->max() ?? 4099;
            
            // Generate next available code (4100, 4200, 4300, etc.)
            $nextCode = (int) floor(($maxCode + 100) / 100) * 100;
            if ($nextCode < 4100) {
                $nextCode = 4100;
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
     * Sync all paid invoices for a drive (useful for initial setup)
     */
    public function syncAllPaidInvoices(Drive $drive): int
    {
        $paidInvoices = $drive->invoices()
            ->where('status', 'paid')
            ->get();

        $synced = 0;
        foreach ($paidInvoices as $invoice) {
            try {
                $this->syncInvoice($invoice);
                $synced++;
            } catch (\Exception $e) {
                Log::error("Failed to sync invoice {$invoice->invoice_number}: " . $e->getMessage());
            }
        }

        return $synced;
    }
}

