<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'parent_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'start_date',
        'estimated_hours',
        'actual_hours',
        'sort_order',
        'owner_id',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'start_date' => 'date',
        'estimated_hours' => 'integer',
        'actual_hours' => 'integer',
        'sort_order' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id')->orderBy('sort_order');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_members')
            ->withTimestamps()
            ->using(TaskMember::class);
    }

    public function taskMembers(): HasMany
    {
        return $this->hasMany(TaskMember::class);
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(TaskLabel::class, 'task_label_task')
            ->withTimestamps();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function headerImage(): HasMany
    {
        return $this->hasMany(TaskAttachment::class)->where('type', 'header');
    }

    public function taskAttachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class)->where('type', 'attachment');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->whereNull('parent_id')->orderBy('created_at');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at');
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date < now() && !in_array($this->status, ['done', 'blocked']);
    }

    public function isAssignedTo(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }
}
