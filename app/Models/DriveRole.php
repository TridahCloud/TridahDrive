<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DriveRole extends Model
{
    protected $fillable = [
        'drive_id',
        'parent_role_id',
        'name',
        'description',
        'is_inherited',
        'override_permissions',
        'sort_order',
    ];

    protected $casts = [
        'is_inherited' => 'boolean',
        'override_permissions' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the drive this role belongs to
     */
    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }

    /**
     * Get the parent role this inherits from
     */
    public function parentRole(): BelongsTo
    {
        return $this->belongsTo(DriveRole::class, 'parent_role_id');
    }

    /**
     * Get child roles that inherit from this one
     */
    public function childRoles(): HasMany
    {
        return $this->hasMany(DriveRole::class, 'parent_role_id');
    }

    /**
     * Get all permissions for this role
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(DriveRolePermission::class);
    }

    /**
     * Get all assignments for this role
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(DriveRoleAssignment::class);
    }

    /**
     * Get effective permissions (including inherited from parent)
     */
    public function getEffectivePermissions(): array
    {
        $permissions = [];
        
        // If inheriting from parent role, get parent permissions first
        if ($this->is_inherited && $this->parentRole) {
            $permissions = $this->parentRole->getEffectivePermissions();
        }
        
        // Override with this role's permissions
        foreach ($this->permissions as $permission) {
            $permissions[$permission->permission_key] = $permission->permission_value;
        }
        
        return $permissions;
    }

    /**
     * Check if this role has a specific permission
     */
    public function hasPermission(string $permissionKey): bool
    {
        $permissions = $this->getEffectivePermissions();
        
        if (!isset($permissions[$permissionKey])) {
            return false;
        }
        
        $value = $permissions[$permissionKey];
        
        // If boolean, return as-is
        if (is_bool($value)) {
            return $value;
        }
        
        // If array or other value, consider it as "has permission" (specific IDs, etc.)
        return !empty($value);
    }

    /**
     * Get permission value (useful for specific IDs, etc.)
     */
    public function getPermissionValue(string $permissionKey)
    {
        $permissions = $this->getEffectivePermissions();
        return $permissions[$permissionKey] ?? null;
    }
}
