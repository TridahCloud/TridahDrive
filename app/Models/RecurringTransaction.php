<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class RecurringTransaction extends Model
{
    protected $fillable = [
        'drive_id',
        'name',
        'description',
        'amount',
        'type',
        'frequency',
        'start_date',
        'end_date',
        'next_due_date',
        'account_id',
        'category_id',
        'payee',
        'payment_method',
        'notes',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_due_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the drive this recurring transaction belongs to
     */
    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }

    /**
     * Get the account for this recurring transaction
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the category for this recurring transaction
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user who created this recurring transaction
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Calculate the next due date based on frequency
     */
    public function calculateNextDueDate(?Carbon $fromDate = null): Carbon
    {
        $fromDate = $fromDate ?? ($this->next_due_date ?? $this->start_date);
        
        return match($this->frequency) {
            'daily' => $fromDate->copy()->addDay(),
            'weekly' => $fromDate->copy()->addWeek(),
            'monthly' => $fromDate->copy()->addMonth(),
            'yearly' => $fromDate->copy()->addYear(),
            default => $fromDate->copy()->addMonth(),
        };
    }

    /**
     * Check if this recurring transaction is due or overdue
     */
    public function isDue(): bool
    {
        return $this->is_active 
            && $this->next_due_date <= now()
            && ($this->end_date === null || $this->next_due_date <= $this->end_date);
    }

    /**
     * Check if this recurring transaction is overdue
     */
    public function isOverdue(): bool
    {
        return $this->is_active 
            && $this->next_due_date < now()
            && ($this->end_date === null || $this->next_due_date <= $this->end_date);
    }

    /**
     * Scope for active recurring transactions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for due recurring transactions
     */
    public function scopeDue($query)
    {
        return $query->where('is_active', true)
            ->where('next_due_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhereColumn('next_due_date', '<=', 'end_date');
            });
    }

    /**
     * Scope for upcoming recurring transactions
     */
    public function scopeUpcoming($query, $days = 30)
    {
        return $query->where('is_active', true)
            ->where('next_due_date', '<=', now()->addDays($days))
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhereColumn('next_due_date', '<=', 'end_date');
            });
    }
}
