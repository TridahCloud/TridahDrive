<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DriveRoleAssignment extends Model
{
    protected $fillable = [
        'drive_id',
        'drive_role_id',
        'assignable_type',
        'assignable_id',
    ];

    /**
     * Get the drive this assignment belongs to
     */
    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }

    /**
     * Get the role this assignment references
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(DriveRole::class, 'drive_role_id');
    }

    /**
     * Get the assignable entity (Person or User)
     */
    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }
}
