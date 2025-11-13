<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'drive_id',
        'created_by',
        'name',
        'description',
        'priority',
        'default_labels',
        'default_members',
        'template_description',
        'estimated_hours',
        'is_active',
    ];

    protected $casts = [
        'default_labels' => 'array',
        'default_members' => 'array',
        'estimated_hours' => 'integer',
        'is_active' => 'boolean',
    ];

    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
