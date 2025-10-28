<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriveItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'drive_id',
        'tool_type',
        'name',
        'data',
        'metadata',
        'created_by_id',
        'updated_by_id',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the drive this item belongs to
     */
    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }

    /**
     * Get the user who created this item
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Get the user who last updated this item
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }
}
