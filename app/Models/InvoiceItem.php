<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'amount',
        'item_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the invoice this item belongs to
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Calculate the total amount for this item
     */
    public function calculateAmount(): void
    {
        $this->amount = $this->quantity * $this->unit_price;
        $this->save();
    }

    /**
     * Calculate and update amount
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->amount = $item->quantity * $item->unit_price;
        });
    }
}
