<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'drive_id',
        'people_manager_profile_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'date_of_birth',
        'type',
        'employee_id',
        'job_title',
        'department',
        'hire_date',
        'termination_date',
        'status',
        'pay_type',
        'hourly_rate',
        'salary_amount',
        'salary_frequency',
        'pay_frequency',
        'tax_id',
        'tax_filing_status',
        'tax_exemptions',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'notes',
        'custom_fields',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'hourly_rate' => 'decimal:2',
        'salary_amount' => 'decimal:2',
        'tax_exemptions' => 'integer',
        'custom_fields' => 'array',
    ];

    /**
     * Get the drive this person belongs to
     */
    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }

    /**
     * Get the user this person is linked to (if any)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the people manager profile for this person
     */
    public function peopleManagerProfile(): BelongsTo
    {
        return $this->belongsTo(PeopleManagerProfile::class);
    }

    /**
     * Get all schedules for this person
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Get all time logs for this person
     */
    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    /**
     * Get all payroll entries for this person
     */
    public function payrollEntries(): HasMany
    {
        return $this->hasMany(PayrollEntry::class);
    }

    /**
     * Get all projects this person is assigned to
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'person_project')
            ->withTimestamps();
    }

    /**
     * Get role assignments for this person
     */
    public function roleAssignments(): HasMany
    {
        return $this->hasMany(DriveRoleAssignment::class, 'assignable_id')
            ->where('assignable_type', Person::class);
    }

    /**
     * Get the role assigned to this person in their drive
     */
    public function getRoleAttribute(): ?DriveRole
    {
        return $this->drive->getRoleForPerson($this);
    }

    /**
     * Get the person's full name
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Scope to get only active people
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the effective hourly rate for this person
     * Converts salary to hourly equivalent if needed
     */
    public function getEffectiveHourlyRate(): ?float
    {
        if ($this->pay_type === 'hourly' && $this->hourly_rate) {
            return (float) $this->hourly_rate;
        }

        if ($this->pay_type === 'salary' && $this->salary_amount && $this->salary_frequency) {
            // Convert salary to hourly rate based on frequency
            $annualSalary = match($this->salary_frequency) {
                'weekly' => $this->salary_amount * 52,
                'biweekly' => $this->salary_amount * 26,
                'monthly' => $this->salary_amount * 12,
                'annually' => $this->salary_amount,
                default => null,
            };

            if ($annualSalary) {
                // Assume 2080 hours per year (40 hours/week * 52 weeks)
                return round($annualSalary / 2080, 2);
            }
        }

        return null;
    }

    /**
     * Check if person can have pay calculated (has pay rate set)
     */
    public function canCalculatePay(): bool
    {
        if ($this->pay_type === 'hourly' && $this->hourly_rate) {
            return true;
        }

        if ($this->pay_type === 'salary' && $this->salary_amount && $this->salary_frequency) {
            return true;
        }

        return false;
    }

    /**
     * Calculate prorated salary amount for a date range
     * This method calculates salary based on how many complete pay periods are covered
     * 
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return float|null
     */
    public function getProratedSalaryForPeriod(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): ?float
    {
        if ($this->pay_type !== 'salary' || !$this->salary_amount || !$this->salary_frequency) {
            return null;
        }

        $daysInPeriod = $startDate->diffInDays($endDate) + 1; // Inclusive

        // Calculate based on salary frequency - how many complete pay periods does this cover?
        return match($this->salary_frequency) {
            'weekly' => $this->calculateWeeklySalary($startDate, $endDate, $daysInPeriod),
            'biweekly' => $this->calculateBiweeklySalary($startDate, $endDate, $daysInPeriod),
            'monthly' => $this->calculateMonthlySalary($startDate, $endDate, $daysInPeriod),
            'annually' => round($this->salary_amount * ($daysInPeriod / 365.25), 2), // Account for leap years
            default => null,
        };
    }

    /**
     * Calculate weekly salary for a period
     */
    protected function calculateWeeklySalary(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, int $daysInPeriod): float
    {
        // Calculate complete weeks in the period
        $completeWeeks = floor($daysInPeriod / 7);
        
        // If it's exactly 7 days (or very close), treat as one full week
        if ($daysInPeriod >= 6 && $daysInPeriod <= 8) {
            return round($this->salary_amount, 2);
        }
        
        // Calculate salary for complete weeks
        $weeksSalary = $completeWeeks * $this->salary_amount;
        
        // Prorate for remaining days if any
        $remainingDays = $daysInPeriod % 7;
        if ($remainingDays > 0) {
            $proratedAmount = ($this->salary_amount / 7) * $remainingDays;
            $weeksSalary += $proratedAmount;
        }
        
        return round($weeksSalary, 2);
    }

    /**
     * Calculate bi-weekly salary for a period
     */
    protected function calculateBiweeklySalary(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, int $daysInPeriod): float
    {
        // Calculate complete bi-weekly periods (14 days each)
        $completePeriods = floor($daysInPeriod / 14);
        
        // If it's exactly 14 days (or very close), treat as one full bi-weekly period
        if ($daysInPeriod >= 13 && $daysInPeriod <= 15) {
            return round($this->salary_amount, 2);
        }
        
        // Calculate salary for complete bi-weekly periods
        $periodsSalary = $completePeriods * $this->salary_amount;
        
        // Prorate for remaining days if any
        $remainingDays = $daysInPeriod % 14;
        if ($remainingDays > 0) {
            $proratedAmount = ($this->salary_amount / 14) * $remainingDays;
            $periodsSalary += $proratedAmount;
        }
        
        return round($periodsSalary, 2);
    }

    /**
     * Calculate monthly salary for a period
     */
    protected function calculateMonthlySalary(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, int $daysInPeriod): float
    {
        // Check if the period aligns with calendar months
        $startMonth = $startDate->format('Y-m');
        $endMonth = $endDate->format('Y-m');
        
        // If start and end are in the same month, check if it covers the full month
        if ($startMonth === $endMonth) {
            $monthStart = $startDate->copy()->startOfMonth();
            $monthEnd = $startDate->copy()->endOfMonth();
            
            // If period covers the full month (or very close), give full monthly salary
            $daysFromStart = abs($startDate->diffInDays($monthStart));
            $daysFromEnd = abs($endDate->diffInDays($monthEnd));
            
            if ($daysFromStart <= 1 && $daysFromEnd <= 1) {
                return round($this->salary_amount, 2);
            }
            
            // Single month - prorate based on days in the month
            $daysInMonth = $startDate->daysInMonth;
            $proratedAmount = ($this->salary_amount / $daysInMonth) * $daysInPeriod;
            return round($proratedAmount, 2);
        }
        
        // Calculate based on calendar months
        $monthsDiff = $startDate->diffInMonths($endDate);
        $totalSalary = 0;
        
        // First month (partial)
        $firstMonthStart = $startDate->copy()->startOfMonth();
        $firstMonthEnd = $startDate->copy()->endOfMonth();
        $firstMonthDays = $startDate->diffInDays($firstMonthEnd) + 1;
        $firstMonthSalary = ($this->salary_amount / $startDate->daysInMonth) * $firstMonthDays;
        $totalSalary += $firstMonthSalary;
        
        // Full months in between
        if ($monthsDiff > 1) {
            // Add full salary for each complete month between start and end
            $currentDate = $startDate->copy()->addMonth()->startOfMonth();
            while ($currentDate->format('Y-m') < $endDate->format('Y-m')) {
                $totalSalary += $this->salary_amount;
                $currentDate->addMonth();
            }
        }
        
        // Last month (partial)
        $lastMonthStart = $endDate->copy()->startOfMonth();
        $lastMonthDays = $lastMonthStart->diffInDays($endDate) + 1;
        $lastMonthSalary = ($this->salary_amount / $endDate->daysInMonth) * $lastMonthDays;
        $totalSalary += $lastMonthSalary;
        
        return round($totalSalary, 2);
    }
}
