<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserItem extends Model
{
    protected $fillable = [
        'user_id',
        'drive_id',
        'name',
        'description',
        'unit',
        'default_price',
    ];

    protected $casts = [
        'default_price' => 'decimal:2',
    ];

    /**
     * Get the user that owns this item
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the drive this item belongs to
     */
    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }
}
