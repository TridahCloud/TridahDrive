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
