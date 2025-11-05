<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'drive_id',
        'user_id',
        'client_id',
        'invoice_profile_id',
        'invoice_number',
        'client_name',
        'client_address',
        'client_email',
        'project',
        'issue_date',
        'due_date',
        'status',
        'notes',
        'customizations',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'customizations' => 'array',
    ];

    /**
     * Get the drive this invoice belongs to
     */
    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }

    /**
     * Get the user that created this invoice
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the client for this invoice
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the invoice profile used for this invoice
     */
    public function invoiceProfile(): BelongsTo
    {
        return $this->belongsTo(InvoiceProfile::class);
    }

    /**
     * Get all items for this invoice
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('item_order');
    }

    /**
     * Get the BookKeeper transaction associated with this invoice (if paid)
     */
    public function bookTransaction()
    {
        return BookTransaction::where('drive_id', $this->drive_id)
            ->where('reference', $this->invoice_number)
            ->first();
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' 
            && $this->status !== 'cancelled' 
            && $this->due_date < now();
    }

    /**
     * Calculate totals
     */
    public function calculateTotals(): void
    {
        $subtotal = $this->items()->sum('amount');
        $taxAmount = $subtotal * ($this->tax_rate / 100);
        $total = $subtotal + $taxAmount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);
    }
}
