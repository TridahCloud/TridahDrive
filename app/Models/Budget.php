<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    protected $fillable = [
        'drive_id',
        'name',
        'description',
        'category_id',
        'period_type',
        'amount',
        'start_date',
        'end_date',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the drive this budget belongs to
     */
    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }

    /**
     * Get the category for this budget (optional)
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user who created this budget
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all transactions for this budget
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(BookTransaction::class);
    }

    /**
     * Calculate the total spent for this budget
     * Only counts expense transactions (not income, transfer, or adjustment)
     */
    public function getTotalSpentAttribute()
    {
        if (!$this->relationLoaded('transactions')) {
            return (float) $this->transactions()
                ->where('type', 'expense')
                ->where('status', '!=', 'pending')
                ->sum('amount');
        }
        
        // When transactions are loaded as a collection, use filter() instead of where()
        return (float) $this->transactions
            ->filter(function ($transaction) {
                return $transaction->type === 'expense' && $transaction->status !== 'pending';
            })
            ->sum('amount');
    }

    /**
     * Check if budget is over budget
     */
    public function getIsOverBudgetAttribute()
    {
        return $this->total_spent > $this->amount;
    }

    /**
     * Calculate the remaining budget
     */
    public function getRemainingAttribute()
    {
        return $this->amount - $this->total_spent;
    }

    /**
     * Calculate the percentage used
     */
    public function getPercentageUsedAttribute()
    {
        if ($this->amount == 0) {
            return 0;
        }
        return min(100, ($this->total_spent / $this->amount) * 100);
    }

}

