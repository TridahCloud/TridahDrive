<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskCustomFieldValue extends Model
{
    protected $fillable = [
        'task_id',
        'field_definition_id',
        'value',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function fieldDefinition(): BelongsTo
    {
        return $this->belongsTo(TaskCustomFieldDefinition::class, 'field_definition_id');
    }
}
