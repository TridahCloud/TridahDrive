<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Drive extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'owner_id',
        'parent_drive_id',
        'color',
        'icon',
        'settings',
        'description',
        'currency',
        'timezone',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    /**
     * Get the owner of the drive
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the parent drive (if this is a sub-drive)
     */
    public function parentDrive(): BelongsTo
    {
        return $this->belongsTo(Drive::class, 'parent_drive_id');
    }

    /**
     * Get all sub-drives of this drive
     */
    public function subDrives(): HasMany
    {
        return $this->hasMany(Drive::class, 'parent_drive_id');
    }

    /**
     * Get all users who have access to this drive
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'drive_users')
            ->withPivot('role', 'invited_by_id', 'joined_at')
            ->withTimestamps()
            ->using(DriveUser::class);
    }

    /**
     * Get all memberships (drive_users pivot records)
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(DriveUser::class);
    }

    /**
     * Get all items in this drive
     */
    public function items(): HasMany
    {
        return $this->hasMany(DriveItem::class);
    }

    /**
     * Get all tool profiles for this drive
     */
    public function toolProfiles(): HasMany
    {
        return $this->hasMany(ToolProfile::class);
    }

    /**
     * Get invoice profiles for this drive
     */
    public function invoiceProfiles(): HasMany
    {
        return $this->hasMany(InvoiceProfile::class);
    }

    /**
     * Get invoices for this drive
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get clients for this drive
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Get user items for this drive
     */
    public function userItems(): HasMany
    {
        return $this->hasMany(UserItem::class);
    }

    /**
     * Get accounts for this drive (BookKeeper)
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Get categories for this drive (BookKeeper)
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get transactions for this drive (BookKeeper)
     */
    public function bookTransactions(): HasMany
    {
        return $this->hasMany(BookTransaction::class);
    }

    /**
     * Get budgets for this drive (BookKeeper)
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Get recurring transactions for this drive (BookKeeper)
     */
    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    /**
     * Get departments for this drive (BookKeeper)
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Get projects for this drive (Project Board)
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get task labels for this drive (Project Board)
     */
    public function taskLabels(): HasMany
    {
        return $this->hasMany(TaskLabel::class);
    }

    /**
     * Get people manager profiles for this drive
     */
    public function peopleManagerProfiles(): HasMany
    {
        return $this->hasMany(PeopleManagerProfile::class);
    }

    /**
     * Get people (employees/volunteers) for this drive
     */
    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
    }

    /**
     * Get schedules for this drive
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Get time logs for this drive
     */
    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    /**
     * Get payroll entries for this drive
     */
    public function payrollEntries(): HasMany
    {
        return $this->hasMany(PayrollEntry::class);
    }

    /**
     * Get the default invoice profile
     */
    public function getDefaultInvoiceProfileAttribute(): ?InvoiceProfile
    {
        return $this->invoiceProfiles()->where('is_default', true)->first() 
            ?? $this->invoiceProfiles()->first();
    }

    /**
     * Get the default people manager profile
     */
    public function getDefaultPeopleManagerProfileAttribute(): ?PeopleManagerProfile
    {
        return $this->peopleManagerProfiles()->where('is_default', true)->first() 
            ?? $this->peopleManagerProfiles()->first();
    }

    /**
     * Check if a user is a member of this drive
     */
    public function hasMember(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the user's role in this drive
     */
    public function getUserRole(User $user): ?string
    {
        $membership = $this->memberships()->where('user_id', $user->id)->first();
        return $membership?->role;
    }

    /**
     * Check if user has permission (owner or admin)
     */
    public function isOwnerOrAdmin(User $user): bool
    {
        $role = $this->getUserRole($user);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Check if user can edit
     */
    public function canEdit(User $user): bool
    {
        $role = $this->getUserRole($user);
        return in_array($role, ['owner', 'admin', 'member']);
    }

    /**
     * Get all drive IDs including this drive and sub-drives
     */
    public function getDriveIdsIncludingSubDrives(bool $includeHidden = false): array
    {
        $driveIds = [$this->id];

        $subDriveIds = $this->subDrives()
            ->when(!$includeHidden, function($query) {
                $settings = $this->settings ?? [];
                $hiddenSubDrives = $settings['hidden_sub_drives'] ?? [];
                if (!empty($hiddenSubDrives)) {
                    $query->whereNotIn('id', $hiddenSubDrives);
                }
            })
            ->pluck('id')
            ->toArray();

        return array_merge($driveIds, $subDriveIds);
    }

    /**
     * Get all invoices including from sub-drives
     */
    public function getInvoicesIncludingSubDrives(bool $includeHidden = false)
    {
        $driveIds = $this->getDriveIdsIncludingSubDrives($includeHidden);
        return Invoice::whereIn('drive_id', $driveIds);
    }

    /**
     * Get all transactions including from sub-drives
     */
    public function getTransactionsIncludingSubDrives(bool $includeHidden = false)
    {
        $driveIds = $this->getDriveIdsIncludingSubDrives($includeHidden);
        return BookTransaction::whereIn('drive_id', $driveIds);
    }

    /**
     * Get all projects including from sub-drives
     */
    public function getProjectsIncludingSubDrives(bool $includeHidden = false)
    {
        $driveIds = $this->getDriveIdsIncludingSubDrives($includeHidden);
        return Project::whereIn('drive_id', $driveIds);
    }

    /**
     * Get all people including from sub-drives
     */
    public function getPeopleIncludingSubDrives(bool $includeHidden = false)
    {
        $driveIds = $this->getDriveIdsIncludingSubDrives($includeHidden);
        return Person::whereIn('drive_id', $driveIds);
    }

    /**
     * Get all schedules including from sub-drives
     */
    public function getSchedulesIncludingSubDrives(bool $includeHidden = false)
    {
        $driveIds = $this->getDriveIdsIncludingSubDrives($includeHidden);
        return Schedule::whereIn('drive_id', $driveIds);
    }

    /**
     * Get all time logs including from sub-drives
     */
    public function getTimeLogsIncludingSubDrives(bool $includeHidden = false)
    {
        $driveIds = $this->getDriveIdsIncludingSubDrives($includeHidden);
        return TimeLog::whereIn('drive_id', $driveIds);
    }

    /**
     * Get all payroll entries including from sub-drives
     */
    public function getPayrollEntriesIncludingSubDrives(bool $includeHidden = false)
    {
        $driveIds = $this->getDriveIdsIncludingSubDrives($includeHidden);
        return PayrollEntry::whereIn('drive_id', $driveIds);
    }

    /**
     * Get all roles for this drive
     */
    public function roles(): HasMany
    {
        return $this->hasMany(DriveRole::class)->orderBy('sort_order');
    }

    /**
     * Get all role assignments for this drive
     */
    public function roleAssignments(): HasMany
    {
        return $this->hasMany(DriveRoleAssignment::class);
    }

    /**
     * Get roles from parent drive (for inheritance)
     */
    public function getParentRoles()
    {
        if (!$this->parentDrive) {
            return collect([]);
        }
        
        return $this->parentDrive->roles()->get();
    }

    /**
     * Get role assigned to a user (via Person or directly)
     */
    public function getRoleForUser(User $user): ?DriveRole
    {
        // Check if user is directly assigned to a role
        $directAssignment = $this->roleAssignments()
            ->where('assignable_type', User::class)
            ->where('assignable_id', $user->id)
            ->with('role')
            ->first();
        
        if ($directAssignment) {
            return $directAssignment->role;
        }
        
        // Check if user is linked to a Person who has a role assigned
        $person = $this->people()->where('user_id', $user->id)->first();
        if ($person) {
            $personAssignment = $this->roleAssignments()
                ->where('assignable_type', Person::class)
                ->where('assignable_id', $person->id)
                ->with('role')
                ->first();
            
            if ($personAssignment) {
                return $personAssignment->role;
            }
        }
        
        // Check parent drive for inherited roles
        if ($this->parentDrive) {
            return $this->parentDrive->getRoleForUser($user);
        }
        
        return null;
    }

    /**
     * Get role assigned to a person
     */
    public function getRoleForPerson(Person $person): ?DriveRole
    {
        $assignment = $this->roleAssignments()
            ->where('assignable_type', Person::class)
            ->where('assignable_id', $person->id)
            ->with('role')
            ->first();
        
        if ($assignment) {
            return $assignment->role;
        }
        
        // Check parent drive for inherited roles
        if ($this->parentDrive) {
            return $this->parentDrive->getRoleForPerson($person);
        }
        
        return null;
    }

    /**
     * Check if user has a specific permission
     */
    public function userHasPermission(User $user, string $permissionKey): bool
    {
        // Owner always has all permissions
        if ($this->owner_id === $user->id) {
            return true;
        }
        
        // Check drive_users role (legacy system)
        $legacyRole = $this->getUserRole($user);
        if ($legacyRole === 'admin') {
            return true; // Admins have all permissions
        }
        
        // Check assigned role
        $role = $this->getRoleForUser($user);
        if ($role) {
            return $role->hasPermission($permissionKey);
        }
        
        // Default permissions based on legacy role
        return match($legacyRole) {
            'member' => true, // Members can do most things
            'viewer' => in_array($permissionKey, [
                'mytime.view_own_schedules',
                'mytime.view_own_time_logs',
                'project.view_assigned',
            ]),
            default => false,
        };
    }

    /**
     * Check if person has a specific permission
     */
    public function personHasPermission(Person $person, string $permissionKey): bool
    {
        // If person is linked to a user, check user permissions
        if ($person->user_id) {
            return $this->userHasPermission($person->user, $permissionKey);
        }
        
        // Check assigned role
        $role = $this->getRoleForPerson($person);
        if ($role) {
            return $role->hasPermission($permissionKey);
        }
        
        return false;
    }

    /**
     * Check if user has permission to view a specific project
     */
    public function userCanViewProject(User $user, Project $project): bool
    {
        // First check project-level permissions (for users shared directly to project)
        if ($project->userCanView($user)) {
            return true;
        }
        
        // Owner and admin can view all
        if ($this->owner_id === $user->id || $this->getUserRole($user) === 'admin') {
            return true;
        }
        
        // Check role permission
        $role = $this->getRoleForUser($user);
        if ($role) {
            $permissionValue = $role->getPermissionValue('project.view_all');
            if ($permissionValue === true) {
                return true;
            }
            
            // Check specific project IDs
            $projectIds = $role->getPermissionValue('project.view_specific');
            if (is_array($projectIds) && in_array($project->id, $projectIds)) {
                return true;
            }
            
            // Check if user is assigned to any task in this project OR assigned to the project itself
            if ($role->hasPermission('project.view_assigned')) {
                // Check if user is assigned to any task in this project
                $hasTaskAssignment = $project->tasks()->whereHas('members', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->exists();
                
                // Check if user is assigned to the project itself
                // Load users relationship if not already loaded to avoid N+1 queries
                if (!$project->relationLoaded('users')) {
                    $project->load('users');
                }
                $hasProjectAssignment = $project->users->pluck('id')->contains($user->id);
                
                return $hasTaskAssignment || $hasProjectAssignment;
            }
        }
        
        // Legacy: members can view all projects
        if ($this->getUserRole($user) === 'member') {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user has permission to view a specific app/section
     */
    public function userCanViewApp(User $user, string $appName): bool
    {
        // Owner always has access to all apps
        if ($this->owner_id === $user->id) {
            return true;
        }
        
        // Check drive_users role (legacy system)
        $legacyRole = $this->getUserRole($user);
        if ($legacyRole === 'admin') {
            return true; // Admins have access to all apps
        }
        
        // Check assigned role
        $role = $this->getRoleForUser($user);
        if ($role) {
            return $role->hasPermission("{$appName}.view");
        }
        
        // Default permissions based on legacy role
        return match($legacyRole) {
            'member' => true, // Members can access all apps by default
            'viewer' => in_array($appName, ['mytime', 'project_board']), // Viewers can only access MyTime and Project Board
            default => false,
        };
    }

    /**
     * Check if user can view BookKeeper
     */
    public function userCanViewBookKeeper(User $user): bool
    {
        return $this->userCanViewApp($user, 'bookkeeper');
    }

    /**
     * Check if user can view Invoicer
     */
    public function userCanViewInvoicer(User $user): bool
    {
        return $this->userCanViewApp($user, 'invoicer');
    }

    /**
     * Check if user can view People Manager
     */
    public function userCanViewPeopleManager(User $user): bool
    {
        return $this->userCanViewApp($user, 'people_manager');
    }

    /**
     * Check if user can view MyTime
     */
    public function userCanViewMyTime(User $user): bool
    {
        // Owner always has access
        if ($this->owner_id === $user->id) {
            return true;
        }
        
        // Check drive_users role (legacy system)
        $legacyRole = $this->getUserRole($user);
        if ($legacyRole === 'admin') {
            return true; // Admins have access to all apps
        }
        
        // Check assigned role
        $role = $this->getRoleForUser($user);
        if ($role) {
            // Check app-level permission
            if ($role->hasPermission('mytime.view')) {
                return true;
            }
            // Also check if they have any MyTime-specific permissions
            if ($role->hasPermission('mytime.view_own_schedules') || $role->hasPermission('mytime.view_own_time_logs')) {
                return true;
            }
        }
        
        // Default permissions based on legacy role
        return match($legacyRole) {
            'member' => true, // Members can access all apps by default
            'viewer' => true, // Viewers can access MyTime
            default => false,
        };
    }

    /**
     * Check if user can view Project Board
     * This checks role-based permissions, not project-level permissions
     * Project-level permissions are checked separately in the controller
     */
    public function userCanViewProjectBoard(User $user): bool
    {
        // Owner always has access
        if ($this->owner_id === $user->id) {
            return true;
        }
        
        // Check drive_users role (legacy system)
        $legacyRole = $this->getUserRole($user);
        if ($legacyRole === 'admin') {
            return true; // Admins have access to all apps
        }
        
        // Check assigned role
        $role = $this->getRoleForUser($user);
        if ($role) {
            // Check app-level permission
            if ($role->hasPermission('project_board.view')) {
                return true;
            }
            // Also check if they have any Project Board-specific permissions
            if ($role->hasPermission('project.view_all') || $role->hasPermission('project.view_assigned') || $role->getPermissionValue('project.view_specific')) {
                return true;
            }
        }
        
        // Default permissions based on legacy role
        return match($legacyRole) {
            'member' => true, // Members can access all apps by default
            'viewer' => true, // Viewers can access Project Board
            default => false,
        };
    }

    /**
     * Check if user can view their own schedules/time logs
     */
    public function userCanViewOwnTime(User $user): bool
    {
        return $this->userHasPermission($user, 'mytime.view_own_schedules') 
            || $this->userHasPermission($user, 'mytime.view_own_time_logs');
    }

    /**
     * Check if this is a sub-drive
     */
    public function isSubDrive(): bool
    {
        return !is_null($this->parent_drive_id);
    }

    /**
     * Get a short identifier for sub-drive transaction numbers
     */
    public function getSubDrivePrefix(): ?string
    {
        if (!$this->isSubDrive()) {
            return null;
        }
        
        // Use first 3 uppercase letters of drive name or drive ID as fallback
        $name = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $this->name));
        if (strlen($name) >= 3) {
            return substr($name, 0, 3);
        }
        
        return str_pad($this->id, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get the effective timezone for this drive (inherits from parent if not set)
     */
    public function getEffectiveTimezone(): string
    {
        if ($this->timezone) {
            return $this->timezone;
        }
        
        // If sub-drive, inherit from parent
        if ($this->parent_drive_id && $this->parentDrive) {
            return $this->parentDrive->getEffectiveTimezone();
        }
        
        // Default to UTC
        return 'UTC';
    }

    /**
     * Convert a date/time to the user's timezone for display
     * Database stores times in UTC, so we convert from UTC to user timezone
     */
    public function toUserTimezone(\Carbon\Carbon $date, ?User $user = null): \Carbon\Carbon
    {
        return \App\Helpers\TimezoneHelper::toUserTimezone($date, $this, $user);
    }

    /**
     * Parse a date/time from user input (assumed in user's timezone) to UTC for storage
     */
    public function parseFromUserInput(string $dateTime, ?User $user = null): \Carbon\Carbon
    {
        return \App\Helpers\TimezoneHelper::parseFromUserInput($dateTime, $this, $user);
    }

    /**
     * Format a date/time in the user's timezone
     * Database stores times in UTC, so we convert from UTC to user timezone
     */
    public function formatForUser(\Carbon\Carbon $date, string $format = 'Y-m-d H:i:s', ?User $user = null): string
    {
        return \App\Helpers\TimezoneHelper::formatForUser($date, $this, $format, $user);
    }
}
