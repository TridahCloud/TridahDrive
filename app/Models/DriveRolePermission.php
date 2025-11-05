<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriveRolePermission extends Model
{
    protected $fillable = [
        'drive_role_id',
        'permission_key',
        'permission_value',
    ];

    protected $casts = [
        'permission_value' => 'json',
    ];

    /**
     * Get the role this permission belongs to
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(DriveRole::class, 'drive_role_id');
    }
}
