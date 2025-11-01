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
        'frequency_interval',
        'frequency_day_of_week',
        'frequency_day_of_month',
        'frequency_week_of_month',
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
        'frequency_interval' => 'integer',
        'frequency_day_of_week' => 'integer',
        'frequency_day_of_month' => 'integer',
        'frequency_week_of_month' => 'integer',
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
     * Calculate the next due date based on frequency and advanced options
     */
    public function calculateNextDueDate(?Carbon $fromDate = null): Carbon
    {
        $fromDate = $fromDate ?? ($this->next_due_date ?? $this->start_date);
        $interval = $this->frequency_interval ?? 1;
        $date = $fromDate->copy();
        
        // Handle specific day of month (e.g., 15th of every month)
        if ($this->frequency === 'monthly' && $this->frequency_day_of_month !== null) {
            // If there's a week of month specified (e.g., first Monday, second Friday)
            if ($this->frequency_week_of_month !== null && $this->frequency_day_of_week !== null) {
                return $this->calculateNthWeekdayOfMonth($date, $this->frequency_week_of_month, $this->frequency_day_of_week, $interval);
            }
            
            // Just specific day of month
            $dayOfMonth = $this->frequency_day_of_month;
            $nextDate = $date->copy()->startOfMonth();
            
            // Ensure the day exists in the month (e.g., handle Feb 30th -> Feb 28th)
            $daysInMonth = $nextDate->daysInMonth;
            $dayToUse = min($dayOfMonth, $daysInMonth);
            $nextDate->day($dayToUse);
            
            // If the date is in the past or today, move to next interval
            if ($nextDate <= $date) {
                $nextDate->addMonths($interval);
                // Recalculate for the new month
                $daysInNewMonth = $nextDate->daysInMonth;
                $dayToUse = min($dayOfMonth, $daysInNewMonth);
                $nextDate->day($dayToUse);
            }
            return $nextDate;
        }
        
        // Handle specific day of week (e.g., every Monday, every 2 weeks on Friday)
        if ($this->frequency_day_of_week !== null) {
            if ($this->frequency === 'weekly') {
                return $this->calculateNextWeekday($date, $this->frequency_day_of_week, $interval);
            } elseif ($this->frequency === 'monthly' && $this->frequency_week_of_month !== null) {
                return $this->calculateNthWeekdayOfMonth($date, $this->frequency_week_of_month, $this->frequency_day_of_week, $interval);
            }
        }
        
        // Default behavior with interval support
        return match($this->frequency) {
            'daily' => $date->addDays($interval),
            'weekly' => $date->addWeeks($interval),
            'monthly' => $date->addMonths($interval),
            'yearly' => $date->addYears($interval),
            default => $date->addMonths($interval),
        };
    }
    
    /**
     * Calculate next occurrence of a specific weekday (e.g., next Monday, or Monday in 2 weeks)
     */
    private function calculateNextWeekday(Carbon $fromDate, int $dayOfWeek, int $interval): Carbon
    {
        // dayOfWeek: 0=Sunday, 1=Monday, ..., 6=Saturday
        $currentDayOfWeek = $fromDate->dayOfWeek;
        
        // Calculate days until next occurrence
        $daysUntilNext = ($dayOfWeek - $currentDayOfWeek + 7) % 7;
        
        // If it's the same day, move to next interval
        if ($daysUntilNext === 0) {
            $daysUntilNext = 7 * $interval;
        } else {
            // First occurrence of the weekday, then add intervals
            $daysUntilNext += 7 * ($interval - 1);
        }
        
        return $fromDate->copy()->addDays($daysUntilNext);
    }
    
    /**
     * Calculate nth weekday of month (e.g., first Monday, second Friday, last Wednesday)
     * 
     * @param Carbon $fromDate
     * @param int $weekOfMonth 1=first, 2=second, 3=third, 4=fourth, 5=last
     * @param int $dayOfWeek 0=Sunday, 1=Monday, ..., 6=Saturday
     * @param int $interval Number of months between occurrences
     * @return Carbon
     */
    private function calculateNthWeekdayOfMonth(Carbon $fromDate, int $weekOfMonth, int $dayOfWeek, int $interval): Carbon
    {
        $targetMonth = $fromDate->copy()->startOfMonth();
        $originalMonth = $targetMonth->copy();
        
        // If weekOfMonth is 5 (last), find the last occurrence
        if ($weekOfMonth === 5) {
            $targetMonth->endOfMonth();
            while ($targetMonth->dayOfWeek !== $dayOfWeek && $targetMonth->month === $originalMonth->month) {
                $targetMonth->subDay();
            }
        } else {
            // Find the first occurrence of the weekday in the month
            while ($targetMonth->dayOfWeek !== $dayOfWeek) {
                $targetMonth->addDay();
            }
            // Move to the nth occurrence (nth week)
            if ($weekOfMonth > 1) {
                $targetMonth->addWeeks($weekOfMonth - 1);
            }
            // Ensure we're still in the same month
            if ($targetMonth->month !== $originalMonth->month) {
                // If we went beyond the month, go back to start of next month
                $targetMonth = $originalMonth->copy()->endOfMonth();
                while ($targetMonth->dayOfWeek !== $dayOfWeek) {
                    $targetMonth->subDay();
                }
            }
        }
        
        // If the calculated date is in the past or today, move to next interval
        if ($targetMonth <= $fromDate) {
            $nextMonth = $fromDate->copy()->addMonths($interval)->startOfMonth();
            // Recalculate for the new month
            return $this->calculateNthWeekdayOfMonth($nextMonth, $weekOfMonth, $dayOfWeek, 1);
        }
        
        return $targetMonth;
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
