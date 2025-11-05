<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'drive_id',
        'person_id',
        'schedule_id',
        'work_date',
        'clock_in',
        'clock_out',
        'regular_hours',
        'overtime_hours',
        'break_hours',
        'total_hours',
        'regular_pay',
        'overtime_pay',
        'total_pay',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'clock_in_location',
        'clock_out_location',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_out_latitude',
        'clock_out_longitude',
        'notes',
        'custom_fields',
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'approved_at' => 'datetime',
        'regular_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'break_hours' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'regular_pay' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'total_pay' => 'decimal:2',
        'clock_in_latitude' => 'decimal:8',
        'clock_in_longitude' => 'decimal:8',
        'clock_out_latitude' => 'decimal:8',
        'clock_out_longitude' => 'decimal:8',
        'custom_fields' => 'array',
    ];

    /**
     * Get the drive this time log belongs to
     */
    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }

    /**
     * Get the person for this time log
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Get the schedule for this time log
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Get the user who approved this time log
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the timezone for this time log (from schedule or drive default)
     */
    public function getTimezone(): string
    {
        // Load schedule if not already loaded
        if (!$this->relationLoaded('schedule')) {
            $this->load('schedule');
        }
        
        if ($this->schedule && $this->schedule->timezone) {
            return $this->schedule->timezone;
        }
        
        // Default to UTC if no schedule timezone
        return 'UTC';
    }

    /**
     * Get clock in time in the schedule's timezone
     */
    public function getClockInLocalAttribute(): ?string
    {
        if (!$this->clock_in) {
            return null;
        }
        
        return \Carbon\Carbon::parse($this->clock_in)
            ->setTimezone($this->getTimezone())
            ->format('Y-m-d H:i:s');
    }

    /**
     * Get clock out time in the schedule's timezone
     */
    public function getClockOutLocalAttribute(): ?string
    {
        if (!$this->clock_out) {
            return null;
        }
        
        return \Carbon\Carbon::parse($this->clock_out)
            ->setTimezone($this->getTimezone())
            ->format('Y-m-d H:i:s');
    }

    /**
     * Calculate hours and pay based on clock in/out times or manual hours
     */
    public function calculateHoursAndPay(): void
    {
        // If clock in/out provided, calculate from those
        if ($this->clock_in && $this->clock_out) {
        $start = \Carbon\Carbon::parse($this->clock_in);
        $end = \Carbon\Carbon::parse($this->clock_out);
        
        $totalMinutes = $start->diffInMinutes($end);
            $breakMinutes = ($this->break_hours ?? 0) * 60;
        $workedMinutes = $totalMinutes - $breakMinutes;
        
        $this->total_hours = round($workedMinutes / 60, 2);
        }

        // If total_hours is not set, can't calculate pay
        if (!$this->total_hours || $this->total_hours <= 0) {
            return;
        }

        // Load person relationship if not already loaded
        if (!$this->relationLoaded('person')) {
            $this->load('person');
        }

        $person = $this->person;
        if (!$person) {
            return;
        }
        
        // Determine regular vs overtime hours based on person's profile
            $profile = $person->peopleManagerProfile ?? $person->drive->peopleManagerProfiles()->where('is_default', true)->first();
            $overtimeThreshold = $profile ? $profile->default_overtime_threshold : 40;
            
            // Get hours worked this week for overtime calculation
            $weekStart = $this->work_date->copy()->startOfWeek();
            $weekEnd = $this->work_date->copy()->endOfWeek();
            
            $weeklyHours = $this->person->timeLogs()
                ->whereBetween('work_date', [$weekStart, $weekEnd])
                ->where('id', '!=', $this->id)
                ->sum('total_hours');
            
            $weeklyHours += $this->total_hours;
            
            if ($weeklyHours > $overtimeThreshold) {
                $regularFromThisLog = max(0, $overtimeThreshold - ($weeklyHours - $this->total_hours));
                $this->regular_hours = round($regularFromThisLog, 2);
                $this->overtime_hours = round($this->total_hours - $this->regular_hours, 2);
            } else {
                $this->regular_hours = $this->total_hours;
                $this->overtime_hours = 0;
            }
            
            // Calculate pay
            if ($person->pay_type === 'hourly' && $person->hourly_rate) {
                $overtimeMultiplier = $profile ? $profile->default_overtime_multiplier : 1.5;
                $this->regular_pay = round($this->regular_hours * $person->hourly_rate, 2);
                $this->overtime_pay = round($this->overtime_hours * $person->hourly_rate * $overtimeMultiplier, 2);
                $this->total_pay = $this->regular_pay + $this->overtime_pay;
        } elseif ($person->pay_type === 'salary' && $person->salary_amount && $person->salary_frequency) {
            // For salary employees, do NOT calculate pay based on hours
            // Salary is paid per pay period regardless of hours worked
            // Pay will be set at payroll generation time based on their salary amount
            // Set to 0 here since database doesn't allow null
            $this->regular_pay = 0;
            $this->overtime_pay = 0;
            $this->total_pay = 0;
        } elseif ($person->pay_type === 'volunteer') {
            // Volunteers don't get paid
            $this->regular_pay = 0;
            $this->overtime_pay = 0;
            $this->total_pay = 0;
        }
    }

    /**
     * Scope to get approved time logs
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get pending time logs
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
