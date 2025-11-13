<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskCustomFieldDefinition extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'slug',
        'type',
        'options',
        'required',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(TaskCustomFieldValue::class, 'field_definition_id');
    }
}
