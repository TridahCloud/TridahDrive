<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\DriveRole;
use App\Models\DriveRolePermission;
use App\Models\DriveRoleAssignment;
use App\Models\Person;
use App\Models\User;
use Illuminate\Http\Request;

class DriveRoleController extends Controller
{
    /**
     * Display a listing of roles for a drive
     */
    public function index(Drive $drive)
    {
        $this->authorize('update', $drive);

        $roles = $drive->roles()->with('permissions')->get();
        $parentRoles = $drive->getParentRoles();

        return view('drives.roles.index', compact('drive', 'roles', 'parentRoles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create(Drive $drive)
    {
        $this->authorize('update', $drive);

        $parentRoles = $drive->getParentRoles();
        $projects = $drive->projects()->orderBy('name')->get();

        return view('drives.roles.create', compact('drive', 'parentRoles', 'projects'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request, Drive $drive)
    {
        $this->authorize('update', $drive);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'parent_role_id' => 'nullable|exists:drive_roles,id',
            'is_inherited' => 'boolean',
            'override_permissions' => 'boolean',
            'permissions' => 'nullable|array',
        ]);

        $role = $drive->roles()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'parent_role_id' => $validated['parent_role_id'] ?? null,
            'is_inherited' => $validated['is_inherited'] ?? false,
            'override_permissions' => $validated['override_permissions'] ?? false,
        ]);

        // Add permissions
        if (isset($validated['permissions']) && is_array($validated['permissions'])) {
            foreach ($validated['permissions'] as $key => $permission) {
                // Handle nested array format from form
                if (is_array($permission)) {
                    $permissionKey = $permission['key'] ?? $key;
                    $permissionValue = $permission['value'] ?? true;
                } else {
                    $permissionKey = $key;
                    $permissionValue = $permission === '1' || $permission === 1 ? true : $permission;
                }
                
                // Handle project IDs array
                if ($permissionKey === 'project.view_specific' && is_array($permissionValue)) {
                    $permissionValue = $permissionValue;
                } elseif ($permissionKey === 'project.view_specific_ids' && is_array($permissionValue)) {
                    $permissionKey = 'project.view_specific';
                    $permissionValue = array_map('intval', $permissionValue);
                }
                
                $role->permissions()->create([
                    'permission_key' => $permissionKey,
                    'permission_value' => is_array($permissionValue) ? $permissionValue : ($permissionValue ?? true),
                ]);
            }
        }

        return redirect()->route('drives.roles.index', $drive)
            ->with('success', 'Role created successfully!');
    }

    /**
     * Display the specified role
     */
    public function show(Drive $drive, DriveRole $role)
    {
        $this->authorize('update', $drive);

        if ($role->drive_id !== $drive->id) {
            abort(404);
        }

        $role->load(['permissions', 'assignments.assignable']);
        $parentRoles = $drive->getParentRoles();

        return view('drives.roles.show', compact('drive', 'role', 'parentRoles'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Drive $drive, DriveRole $role)
    {
        $this->authorize('update', $drive);

        if ($role->drive_id !== $drive->id) {
            abort(404);
        }

        $role->load('permissions');
        $parentRoles = $drive->getParentRoles();
        $projects = $drive->projects()->orderBy('name')->get();
        
        // Get this role's own permissions (not inherited) for displaying current state
        $currentPermissions = [];
        foreach ($role->permissions as $permission) {
            $value = $permission->permission_value;
            // JSON cast handles decoding, but normalize boolean values for checkbox display
            // Keep arrays as-is (for project.view_specific), convert everything else to boolean
            if (!is_array($value)) {
                // Convert to boolean: true, 1, "1", "true" etc. become true
                // false, 0, "0", "false", null, "" etc. become false
                if (is_bool($value)) {
                    // Already boolean, keep as-is
                    $value = $value;
                } elseif (is_numeric($value)) {
                    $value = (bool)$value;
                } elseif (is_string($value)) {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                } else {
                    $value = (bool)$value;
                }
            }
            $currentPermissions[$permission->permission_key] = $value;
        }

        return view('drives.roles.edit', compact('drive', 'role', 'parentRoles', 'projects', 'currentPermissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Drive $drive, DriveRole $role)
    {
        $this->authorize('update', $drive);

        if ($role->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'parent_role_id' => 'nullable|exists:drive_roles,id',
            'is_inherited' => 'boolean',
            'override_permissions' => 'boolean',
            'permissions' => 'nullable|array',
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'parent_role_id' => $validated['parent_role_id'] ?? null,
            'is_inherited' => $validated['is_inherited'] ?? false,
            'override_permissions' => $validated['override_permissions'] ?? false,
        ]);

        // Update permissions (delete and recreate for simplicity)
        $role->permissions()->delete();
        if (isset($validated['permissions']) && is_array($validated['permissions'])) {
            foreach ($validated['permissions'] as $key => $permission) {
                // Handle nested array format from form
                if (is_array($permission)) {
                    $permissionKey = $permission['key'] ?? $key;
                    $permissionValue = $permission['value'] ?? true;
                } else {
                    $permissionKey = $key;
                    $permissionValue = $permission === '1' || $permission === 1 ? true : $permission;
                }
                
                // Handle project IDs array
                if ($permissionKey === 'project.view_specific' && is_array($permissionValue)) {
                    $permissionValue = $permissionValue;
                } elseif ($permissionKey === 'project.view_specific_ids' && is_array($permissionValue)) {
                    $permissionKey = 'project.view_specific';
                    $permissionValue = array_map('intval', $permissionValue);
                }
                
                $role->permissions()->create([
                    'permission_key' => $permissionKey,
                    'permission_value' => is_array($permissionValue) ? $permissionValue : ($permissionValue ?? true),
                ]);
            }
        }

        return redirect()->route('drives.roles.index', $drive)
            ->with('success', 'Role updated successfully!');
    }

    /**
     * Remove the specified role
     */
    public function destroy(Drive $drive, DriveRole $role)
    {
        $this->authorize('update', $drive);

        if ($role->drive_id !== $drive->id) {
            abort(404);
        }

        $role->delete();

        return redirect()->route('drives.roles.index', $drive)
            ->with('success', 'Role deleted successfully!');
    }

    /**
     * Assign a role to a person or user
     */
    public function assignRole(Request $request, Drive $drive)
    {
        $this->authorize('update', $drive);

        $validated = $request->validate([
            'drive_role_id' => 'nullable|exists:drive_roles,id',
            'assignable_type' => 'required|in:App\Models\Person,App\Models\User',
            'assignable_id' => 'required|integer',
        ]);

        // Verify assignable exists
        $assignableClass = $validated['assignable_type'];
        $assignable = $assignableClass::findOrFail($validated['assignable_id']);

        // Verify assignable belongs to this drive
        if ($assignableClass === Person::class && $assignable->drive_id !== $drive->id) {
            abort(403, 'Person does not belong to this drive.');
        }

        // If drive_role_id is empty, remove assignment
        if (empty($validated['drive_role_id'])) {
            DriveRoleAssignment::where('drive_id', $drive->id)
                ->where('assignable_type', $validated['assignable_type'])
                ->where('assignable_id', $validated['assignable_id'])
                ->delete();
            
            return back()->with('success', 'Role assignment removed successfully!');
        }

        // Verify role belongs to this drive
        $role = $drive->roles()->findOrFail($validated['drive_role_id']);

        // Create or update assignment
        DriveRoleAssignment::updateOrCreate(
            [
                'drive_id' => $drive->id,
                'assignable_type' => $validated['assignable_type'],
                'assignable_id' => $validated['assignable_id'],
            ],
            [
                'drive_role_id' => $validated['drive_role_id'],
            ]
        );

        return back()->with('success', 'Role assigned successfully!');
    }

    /**
     * Remove role assignment
     */
    public function removeAssignment(Drive $drive, DriveRoleAssignment $assignment)
    {
        $this->authorize('update', $drive);

        if ($assignment->drive_id !== $drive->id) {
            abort(404);
        }

        $assignment->delete();

        return back()->with('success', 'Role assignment removed successfully!');
    }
}
