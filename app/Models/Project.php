<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\ProjectUser;

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

    public function taskStatuses(): HasMany
    {
        return $this->hasMany(TaskStatus::class)->orderBy('sort_order')->orderBy('id');
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
            ->withPivot('role')
            ->withTimestamps()
            ->using(ProjectUser::class);
    }

    /**
     * Check if a user is a member of this project
     */
    public function hasMember(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the user's role in this project
     */
    public function getUserRole(User $user): ?string
    {
        $membership = $this->users()->where('user_id', $user->id)->first();
        return $membership?->pivot->role ?? null;
    }

    /**
     * Check if user can edit this project
     */
    public function userCanEdit(User $user): bool
    {
        // Project creator can always edit
        if ($this->created_by === $user->id) {
            return true;
        }

        // PROJECT-LEVEL PERMISSIONS TAKE HIGHEST PRIORITY
        // Check project-level permissions first
        $projectRole = $this->getUserRole($user);
        if ($projectRole !== null) {
            // If user has a project-level role, it takes priority
            // Only 'editor' role can edit at project level
            if ($projectRole === 'editor') {
                return true;
            }
            // If they're a 'viewer' at project level, they cannot edit
            // (even if drive-level permissions would allow it)
            if ($projectRole === 'viewer') {
                return false;
            }
        }

        // If no project-level permission, check drive permissions
        if ($this->drive) {
            // Drive owner can always edit
            if ($this->drive->owner_id === $user->id) {
                return true;
            }
            
            // Drive admin can edit
            if ($this->drive->getUserRole($user) === 'admin') {
                return true;
            }
            
            // Drive members can edit if they have edit permission
            if ($this->drive->canEdit($user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user can view this project
     * This checks project-level permissions only (not drive-level)
     * Drive-level permissions are checked in Drive::userCanViewProject
     */
    public function userCanView(User $user): bool
    {
        // Project creator can always view
        if ($this->created_by === $user->id) {
            return true;
        }

        // Check project-level permissions (for users shared directly to project)
        $role = $this->getUserRole($user);
        return in_array($role, ['viewer', 'editor']);
    }

    public function customFieldDefinitions(): HasMany
    {
        return $this->hasMany(TaskCustomFieldDefinition::class)->orderBy('sort_order');
    }

    public function userPreferences(): HasMany
    {
        return $this->hasMany(UserProjectPreference::class);
    }
}
