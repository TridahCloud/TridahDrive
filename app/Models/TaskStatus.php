<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TaskStatus extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'slug',
        'color',
        'sort_order',
        'is_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (TaskStatus $status) {
            if (empty($status->slug)) {
                $status->slug = static::generateUniqueSlug($status->project_id, $status->name, $status->id);
            }
        });
    }

    public static function generateUniqueSlug(int $projectId, string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name, '_');
        if (empty($baseSlug)) {
            $baseSlug = 'status_' . Str::random(6);
        }

        $slug = $baseSlug;
        $counter = 1;

        while (static::where('project_id', $projectId)
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug . '_' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)
            ->orderBy('sort_order')
            ->orderBy('created_at');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}


