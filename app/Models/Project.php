<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'drive_id',
        'name',
        'description',
        'color',
        'header_image_path',
        'header_image_original_name',
        'is_public',
        'public_key',
        'status',
        'start_date',
        'end_date',
        'created_by',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->public_key) && $project->is_public) {
                $project->public_key = static::generatePublicKey();
            }
        });
    }

    public static function generatePublicKey(): string
    {
        do {
            $key = Str::random(32);
        } while (static::where('public_key', $key)->exists());

        return $key;
    }

    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('sort_order')->orderBy('created_at');
    }

    public function activeTasks(): HasMany
    {
        return $this->hasMany(Task::class)->whereNull('deleted_at')->orderBy('sort_order');
    }

    /**
     * Get all people assigned to this project
     */
    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'person_project')
            ->withTimestamps();
    }

    /**
     * Get all users assigned to this project
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withTimestamps();
    }
}
