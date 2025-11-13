<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProjectPreference extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'view',
        'filters',
        'view_settings',
    ];

    protected $casts = [
        'filters' => 'array',
        'view_settings' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
