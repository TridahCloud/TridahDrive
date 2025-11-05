@extends('layouts.dashboard')

@section('title', 'Edit Role - ' . $drive->name)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('drives.index') }}">Drives</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('drives.show', $drive) }}">{{ $drive->name }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('drives.roles.index', $drive) }}">Roles</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-user-shield me-2"></i>Edit Role
                    </h1>
                    <p class="text-muted">{{ $drive->name }} - {{ $role->name }}</p>
                </div>
                <a href="{{ route('drives.roles.index', $drive) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('drives.roles.update', [$drive, $role]) }}" method="POST" id="roleForm">
        @csrf
        @method('PATCH')
        
        <div class="row">
            <div class="col-lg-8">
                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Basic Information</h4>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $role->name) }}" required>
                        <small class="text-muted">e.g., Employee, Manager, Volunteer</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $role->description) }}</textarea>
                        <small class="text-muted">Optional description of this role's purpose</small>
                    </div>
                    
                    @if($drive->parentDrive && $parentRoles->count() > 0)
                        <div class="mb-3">
                            <label for="parent_role_id" class="form-label">Inherit from Parent Role (Optional)</label>
                            <select class="form-select" id="parent_role_id" name="parent_role_id">
                                <option value="">No inheritance</option>
                                @foreach($parentRoles as $parentRole)
                                    <option value="{{ $parentRole->id }}" {{ old('parent_role_id', $role->parent_role_id) == $parentRole->id ? 'selected' : '' }}>
                                        {{ $parentRole->name }} (from {{ $drive->parentDrive->name }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select a role from the parent drive to inherit permissions</small>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_inherited" name="is_inherited" value="1" {{ old('is_inherited', $role->is_inherited) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_inherited">
                                    Inherit permissions from parent role
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="override_permissions" name="override_permissions" value="1" {{ old('override_permissions', $role->override_permissions) ? 'checked' : '' }}>
                                <label class="form-check-label" for="override_permissions">
                                    Allow overriding inherited permissions
                                </label>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Permissions</h4>
                    <p class="text-muted small mb-3">Select what this role can access and do within the Drive.</p>
                    
                    @php
                        // Debug: Show current permissions (uncomment to debug)
                        // dd([
                        //     'currentPermissions' => $currentPermissions,
                        //     'role_permissions' => $role->permissions->pluck('permission_key', 'permission_value')->toArray(),
                        // ]);
                    @endphp
                    
                    <!-- App Access Permissions -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-th-large me-2"></i>App Access Permissions
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">Control which apps/sections users with this role can access.</p>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input permission-checkbox" type="checkbox" 
                                       id="permission_bookkeeper_view" 
                                       name="permissions[bookkeeper.view]" 
                                       value="1" 
                                       {{ old('permissions.bookkeeper.view', !empty($currentPermissions['bookkeeper.view'])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="permission_bookkeeper_view">
                                    <strong>BookKeeper</strong> - View and manage transactions, accounts, categories
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input permission-checkbox" type="checkbox" 
                                       id="permission_invoicer_view" 
                                       name="permissions[invoicer.view]" 
                                       value="1" 
                                       {{ old('permissions.invoicer.view', !empty($currentPermissions['invoicer.view'])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="permission_invoicer_view">
                                    <strong>Invoicer</strong> - View and manage invoices, clients, invoice profiles
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input permission-checkbox" type="checkbox" 
                                       id="permission_people_manager_view" 
                                       name="permissions[people_manager.view]" 
                                       value="1" 
                                       {{ old('permissions.people_manager.view', !empty($currentPermissions['people_manager.view'])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="permission_people_manager_view">
                                    <strong>People Manager</strong> - View and manage people, schedules, time logs, payroll
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input permission-checkbox" type="checkbox" 
                                       id="permission_mytime_view" 
                                       name="permissions[mytime.view]" 
                                       value="1" 
                                       {{ old('permissions.mytime.view', !empty($currentPermissions['mytime.view'])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="permission_mytime_view">
                                    <strong>MyTime</strong> - View own schedules and time logs, clock in/out
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input permission-checkbox" type="checkbox" 
                                       id="permission_project_board_view" 
                                       name="permissions[project_board.view]" 
                                       value="1" 
                                       {{ old('permissions.project_board.view', !empty($currentPermissions['project_board.view'])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="permission_project_board_view">
                                    <strong>Project Board</strong> - View and manage projects and tasks
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- MyTime Permissions -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-clock me-2"></i>MyTime Permissions
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-2">These permissions only apply if MyTime access is granted above.</p>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input permission-checkbox" type="checkbox" 
                                       id="permission_mytime_view_own_schedules" 
                                       name="permissions[mytime.view_own_schedules]" 
                                       value="1" 
                                       {{ old('permissions.mytime.view_own_schedules', !empty($currentPermissions['mytime.view_own_schedules'])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="permission_mytime_view_own_schedules">
                                    <strong>View Own Schedules</strong> - Can view their own assigned schedules
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input permission-checkbox" type="checkbox" 
                                       id="permission_mytime_view_own_time_logs" 
                                       name="permissions[mytime.view_own_time_logs]" 
                                       value="1" 
                                       {{ old('permissions.mytime.view_own_time_logs', !empty($currentPermissions['mytime.view_own_time_logs'])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="permission_mytime_view_own_time_logs">
                                    <strong>View Own Time Logs</strong> - Can view their own time log entries
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Project Permissions -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-project-diagram me-2"></i>Project Permissions
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-2">These permissions only apply if Project Board access is granted above.</p>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input permission-checkbox" type="checkbox" 
                                       id="permission_project_view_all" 
                                       name="permissions[project.view_all]" 
                                       value="1" 
                                       {{ old('permissions.project.view_all', !empty($currentPermissions['project.view_all'])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="permission_project_view_all">
                                    <strong>View All Projects</strong> - Can view all projects in the Drive
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input permission-checkbox" type="checkbox" 
                                       id="permission_project_view_assigned" 
                                       name="permissions[project.view_assigned]" 
                                       value="1" 
                                       {{ old('permissions.project.view_assigned', !empty($currentPermissions['project.view_assigned'])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="permission_project_view_assigned">
                                    <strong>View Assigned Projects</strong> - Can only view projects where they are assigned to tasks
                                </label>
                            </div>
                            
                            @php
                                $hasSpecificProjects = isset($currentPermissions['project.view_specific']) && is_array($currentPermissions['project.view_specific']) && count($currentPermissions['project.view_specific']) > 0;
                                $selectedProjectIds = $hasSpecificProjects ? $currentPermissions['project.view_specific'] : [];
                            @endphp
                            <div class="form-check mb-2">
                                <input class="form-check-input permission-checkbox" type="checkbox" 
                                       id="permission_project_view_specific" 
                                       name="permissions[project.view_specific]" 
                                       value="1"
                                       data-toggle="collapse" 
                                       data-target="#projectSpecificIds"
                                       {{ old('permissions.project.view_specific', $hasSpecificProjects) ? 'checked' : '' }}>
                                <label class="form-check-label" for="permission_project_view_specific">
                                    <strong>View Specific Projects</strong> - Can view only selected projects
                                </label>
                            </div>
                            
                            <div id="projectSpecificIds" class="collapse {{ old('permissions.project.view_specific', $hasSpecificProjects) ? 'show' : '' }} mt-2">
                                <label class="form-label">Select Projects</label>
                                <select class="form-select" id="project_ids" name="permissions[project.view_specific_ids][]" multiple>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ in_array($project->id, old('permissions.project.view_specific_ids', $selectedProjectIds)) ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple projects</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="dashboard-card mb-4">
                    <h5 class="mb-3">Quick Actions</h5>
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save me-2"></i>Update Role
                    </button>
                    <a href="{{ route('drives.roles.index', $drive) }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
                
                <div class="dashboard-card">
                    <h5 class="mb-3">Permission Tips</h5>
                    <ul class="small">
                        <li>Users with roles will have permissions checked before accessing features</li>
                        <li>If no permission is set, the user will be denied access</li>
                        <li>Drive owners and admins always have full access</li>
                        <li>Sub-drives can inherit roles from parent drives</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle permission form submission
    document.getElementById('roleForm').addEventListener('submit', function(e) {
        const formData = new FormData(this);
        const permissions = {};
        
        // Collect permissions
        document.querySelectorAll('.permission-checkbox:checked').forEach(checkbox => {
            const key = checkbox.name.replace('permissions[', '').replace(']', '');
            if (key === 'project.view_specific') {
                // Handle project IDs
                const projectIds = Array.from(document.getElementById('project_ids').selectedOptions).map(opt => parseInt(opt.value));
                if (projectIds.length > 0) {
                    permissions['project.view_specific'] = projectIds;
                }
            } else {
                permissions[key] = true;
            }
        });
        
        // Add hidden inputs for permissions
        Object.keys(permissions).forEach(key => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `permissions[${key}][key]`;
            input.value = key;
            this.appendChild(input);
            
            const valueInput = document.createElement('input');
            valueInput.type = 'hidden';
            valueInput.name = `permissions[${key}][value]`;
            valueInput.value = Array.isArray(permissions[key]) ? JSON.stringify(permissions[key]) : permissions[key];
            this.appendChild(valueInput);
        });
    });
    
    // Handle project.view_specific checkbox
    const projectSpecificCheckbox = document.getElementById('permission_project_view_specific');
    if (projectSpecificCheckbox) {
        projectSpecificCheckbox.addEventListener('change', function() {
            const projectIdsDiv = document.getElementById('projectSpecificIds');
            if (this.checked) {
                projectIdsDiv.classList.add('show');
            } else {
                projectIdsDiv.classList.remove('show');
            }
        });
    }
});
</script>
@endsection

