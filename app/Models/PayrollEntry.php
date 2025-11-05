<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'drive_id',
        'people_manager_profile_id',
        'person_id',
        'payroll_period',
        'period_start_date',
        'period_end_date',
        'pay_date',
        'regular_hours',
        'overtime_hours',
        'total_hours',
        'regular_pay',
        'overtime_pay',
        'bonus',
        'commission',
        'gross_pay',
        'federal_tax',
        'state_tax',
        'local_tax',
        'social_security',
        'medicare',
        'retirement_contribution',
        'health_insurance',
        'other_deductions',
        'total_deductions',
        'net_pay',
        'payment_method',
        'payment_reference',
        'status',
        'book_transaction_id',
        'synced_to_bookkeeper',
        'synced_at',
        'notes',
        'custom_fields',
    ];

    protected $casts = [
        'period_start_date' => 'date',
        'period_end_date' => 'date',
        'pay_date' => 'date',
        'regular_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'regular_pay' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'bonus' => 'decimal:2',
        'commission' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'federal_tax' => 'decimal:2',
        'state_tax' => 'decimal:2',
        'local_tax' => 'decimal:2',
        'social_security' => 'decimal:2',
        'medicare' => 'decimal:2',
        'retirement_contribution' => 'decimal:2',
        'health_insurance' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'synced_to_bookkeeper' => 'boolean',
        'synced_at' => 'datetime',
        'custom_fields' => 'array',
    ];

    /**
     * Get the drive this payroll entry belongs to
     */
    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }

    /**
     * Get the people manager profile for this payroll entry
     */
    public function peopleManagerProfile(): BelongsTo
    {
        return $this->belongsTo(PeopleManagerProfile::class);
    }

    /**
     * Get the person for this payroll entry
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Get the BookKeeper transaction for this payroll entry
     */
    public function bookTransaction(): BelongsTo
    {
        return $this->belongsTo(BookTransaction::class);
    }

    /**
     * Calculate gross pay from components
     */
    public function calculateGrossPay(): void
    {
        $this->gross_pay = $this->regular_pay + $this->overtime_pay + $this->bonus + $this->commission;
    }

    /**
     * Calculate total deductions
     */
    public function calculateTotalDeductions(): void
    {
        $this->total_deductions = $this->federal_tax 
            + $this->state_tax 
            + $this->local_tax 
            + $this->social_security 
            + $this->medicare 
            + $this->retirement_contribution 
            + $this->health_insurance 
            + $this->other_deductions;
    }

    /**
     * Calculate net pay
     */
    public function calculateNetPay(): void
    {
        $this->calculateGrossPay();
        $this->calculateTotalDeductions();
        $this->net_pay = $this->gross_pay - $this->total_deductions;
    }

    /**
     * Scope to get synced payroll entries
     */
    public function scopeSynced($query)
    {
        return $query->where('synced_to_bookkeeper', true);
    }

    /**
     * Scope to get unsynced payroll entries
     */
    public function scopeUnsynced($query)
    {
        return $query->where('synced_to_bookkeeper', false);
    }

    /**
     * Scope to get paid payroll entries
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
