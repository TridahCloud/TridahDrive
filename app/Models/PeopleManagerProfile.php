<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeopleManagerProfile extends Model
{
    protected $fillable = [
        'drive_id',
        'name',
        'is_default',
        'organization_name',
        'organization_address',
        'organization_phone',
        'organization_email',
        'default_pay_frequency',
        'default_overtime_threshold',
        'default_overtime_multiplier',
        'settings',
        'accent_color',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'settings' => 'array',
        'default_overtime_threshold' => 'decimal:2',
        'default_overtime_multiplier' => 'decimal:2',
    ];

    /**
     * Get the drive that owns this profile
     */
    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }

    /**
     * Get all people using this profile
     */
    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
    }

    /**
     * Get all schedules using this profile
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Get all payroll entries using this profile
     */
    public function payrollEntries(): HasMany
    {
        return $this->hasMany(PayrollEntry::class);
    }
}
