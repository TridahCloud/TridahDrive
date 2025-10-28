<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DriveUser extends Pivot
{
    protected $table = 'drive_users';

    protected $fillable = [
        'drive_id',
        'user_id',
        'role',
        'invited_by_id',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    /**
     * Get the drive
     */
    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who invited
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_id');
    }
}
