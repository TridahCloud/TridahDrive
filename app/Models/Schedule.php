<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'drive_id',
        'people_manager_profile_id',
        'person_id',
        'title',
        'description',
        'type',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'timezone',
        'recurrence_pattern',
        'recurrence_days',
        'recurrence_interval',
        'recurrence_end_date',
        'recurrence_count',
        'status',
        'break_minutes',
        'total_hours',
        'location',
        'notes',
        'custom_fields',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'recurrence_days' => 'array',
        'recurrence_end_date' => 'date',
        'break_minutes' => 'integer',
        'total_hours' => 'decimal:2',
        'custom_fields' => 'array',
    ];

    /**
     * Get the drive this schedule belongs to
     */
    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }

    /**
     * Get the people manager profile for this schedule
     */
    public function peopleManagerProfile(): BelongsTo
    {
        return $this->belongsTo(PeopleManagerProfile::class);
    }

    /**
     * Get the person for this schedule
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Get all time logs for this schedule
     */
    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    /**
     * Get the timezone for this schedule (from schedule timezone or drive default)
     */
    public function getTimezone(): string
    {
        if ($this->timezone) {
            return $this->timezone;
        }
        
        // Load drive if not already loaded
        if (!$this->relationLoaded('drive')) {
            $this->load('drive');
        }
        
        if ($this->drive) {
            return $this->drive->getEffectiveTimezone();
        }
        
        return 'UTC';
    }

    /**
     * Format start time for display in user's timezone
     */
    public function getStartTimeForUser(?User $user = null): string
    {
        if (!$this->start_time) {
            return '';
        }
        
        // Get user timezone
        $userTimezone = 'UTC';
        if ($this->drive) {
            $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone($user, $this->drive);
        }
        
        // start_date is stored as a date in UTC, but represents a calendar date
        // start_time is stored as UTC time (e.g., "14:00:00" for 2pm UTC)
        // Combine them: create datetime in UTC
        $dateString = $this->start_date->format('Y-m-d');
        $dateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dateString . ' ' . $this->start_time, 'UTC');
        
        // Convert from UTC to user timezone
        $dateTime->setTimezone($userTimezone);
        
        return $dateTime->format('H:i:s');
    }

    /**
     * Format end time for display in user's timezone
     */
    public function getEndTimeForUser(?User $user = null): string
    {
        if (!$this->end_time) {
            return '';
        }
        
        // Get user timezone
        $userTimezone = 'UTC';
        if ($this->drive) {
            $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone($user, $this->drive);
        }
        
        // end_date or start_date is stored as a date in UTC, but represents a calendar date
        // end_time is stored as UTC time (e.g., "22:00:00" for 10pm UTC)
        // Combine them: create datetime in UTC
        $date = $this->end_date ?? $this->start_date;
        $dateString = $date->format('Y-m-d');
        $dateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dateString . ' ' . $this->end_time, 'UTC');
        
        // Convert from UTC to user timezone
        $dateTime->setTimezone($userTimezone);
        
        return $dateTime->format('H:i:s');
    }

    /**
     * Format start date for display in user's timezone
     * Note: Date-only fields should not be converted through timezones as they represent calendar dates
     */
    public function getStartDateForUser(?User $user = null): string
    {
        if (!$this->start_date) {
            return '';
        }
        
        // Date-only fields should be displayed as-is without timezone conversion
        return $this->start_date->format('M d, Y');
    }

    /**
     * Format end date for display in user's timezone
     * Note: Date-only fields should not be converted through timezones as they represent calendar dates
     */
    public function getEndDateForUser(?User $user = null): string
    {
        if (!$this->end_date) {
            return '';
        }
        
        // Date-only fields should be displayed as-is without timezone conversion
        return $this->end_date->format('M d, Y');
    }

    /**
     * Calculate total hours based on start and end time
     */
    public function calculateTotalHours(): float
    {
        $start = \Carbon\Carbon::parse($this->start_date->format('Y-m-d') . ' ' . $this->start_time);
        $end = \Carbon\Carbon::parse($this->end_date ? $this->end_date->format('Y-m-d') : $this->start_date->format('Y-m-d') . ' ' . $this->end_time);
        
        $totalMinutes = $start->diffInMinutes($end) - $this->break_minutes;
        return round($totalMinutes / 60, 2);
    }
}
