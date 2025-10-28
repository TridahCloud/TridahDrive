<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToolProfile extends Model
{
    protected $fillable = [
        'drive_id',
        'tool_type',
        'name',
        'settings',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Get the drive this profile belongs to
     */
    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }
}
