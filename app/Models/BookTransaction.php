<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookTransaction extends Model
{
    protected $table = 'book_transactions';

    protected $fillable = [
        'drive_id',
        'transaction_number',
        'date',
        'description',
        'reference',
        'amount',
        'type',
        'account_id',
        'category_id',
        'budget_id',
        'payee',
        'payment_method',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_number)) {
                $transaction->transaction_number = static::generateTransactionNumber($transaction->drive_id);
            }
        });
    }

    /**
     * Generate a unique transaction number
     */
    public static function generateTransactionNumber($driveId): string
    {
        $drive = Drive::find($driveId);
        
        // Use sub-drive prefix if available, otherwise use standard TXN prefix
        if ($drive && $drive->isSubDrive()) {
            $subPrefix = $drive->getSubDrivePrefix();
            $prefix = 'TXN-' . $subPrefix;
            
            $lastTransaction = static::where('drive_id', $driveId)
                ->orderBy('id', 'desc')
                ->first();
            
            // Extract number after the sub-drive prefix
            if ($lastTransaction && preg_match('/TXN-' . preg_quote($subPrefix, '/') . '-(\d+)/', $lastTransaction->transaction_number, $matches)) {
                $nextNumber = (int) $matches[1] + 1;
            } else {
                $nextNumber = 1;
            }
            
            return sprintf('%s-%06d', $prefix, $nextNumber);
        }
        
        // Standard TXN numbering for parent drives
        $prefix = 'TXN';
        $lastTransaction = static::where('drive_id', $driveId)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastTransaction && preg_match('/TXN-(\d+)/', $lastTransaction->transaction_number, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }
        
        return sprintf('%s-%06d', $prefix, $nextNumber);
    }

    /**
     * Get the drive this transaction belongs to
     */
    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }

    /**
     * Get the account for this transaction
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the category for this transaction
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the budget for this transaction
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Get the user who created this transaction
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all attachments for this transaction
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(TransactionAttachment::class, 'transaction_id');
    }
}
