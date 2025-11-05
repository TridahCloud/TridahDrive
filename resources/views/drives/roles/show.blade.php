@extends('layouts.dashboard')

@section('title', 'Role Details - ' . $drive->name)

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
                            <li class="breadcrumb-item active">{{ $role->name }}</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-user-shield me-2"></i>{{ $role->name }}
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div>
                    <a href="{{ route('drives.roles.edit', [$drive, $role]) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Role
                    </a>
                    <a href="{{ route('drives.roles.index', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Role Information -->
            <div class="dashboard-card mb-4">
                <h4 class="mb-3 brand-teal">Role Information</h4>
                
                <div class="mb-3">
                    <strong>Description:</strong>
                    <p class="text-muted">{{ $role->description ?? 'No description provided' }}</p>
                </div>
                
                @if($role->is_inherited && $role->parentRole)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This role inherits from: <strong>{{ $role->parentRole->name }}</strong>
                        @if($role->override_permissions)
                            <br><small>Permissions can be overridden</small>
                        @endif
                    </div>
                @endif
                
                <div class="mb-3">
                    <strong>Permissions:</strong>
                    @php
                        $effectivePermissions = $role->getEffectivePermissions();
                    @endphp
                    @if(count($effectivePermissions) > 0)
                        <div class="mt-2">
                            @foreach($effectivePermissions as $key => $value)
                                <div class="mb-2">
                                    <span class="badge bg-success me-2">
                                        {{ str_replace(['.', '_'], [' ', ' '], $key) }}
                                    </span>
                                    @if(is_array($value))
                                        <small class="text-muted">({{ count($value) }} items)</small>
                                    @elseif($value === true)
                                        <small class="text-muted">✓ Enabled</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No permissions assigned</p>
                    @endif
                </div>
            </div>
            
            <!-- Assignments -->
            <div class="dashboard-card">
                <h4 class="mb-3 brand-teal">Role Assignments</h4>
                
                <div class="mb-3">
                    <strong>Assigned to:</strong> {{ $role->assignments()->count() }} person(s)/user(s)
                </div>
                
                @if($role->assignments()->count() > 0)
                    <div class="list-group">
                        @foreach($role->assignments as $assignment)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        @if($assignment->assignable_type === 'App\Models\Person')
                                            <strong>{{ $assignment->assignable->first_name }} {{ $assignment->assignable->last_name }}</strong>
                                            <span class="badge bg-info ms-2">Person</span>
                                        @else
                                            <strong>{{ $assignment->assignable->name }}</strong>
                                            <span class="badge bg-primary ms-2">User</span>
                                        @endif
                                    </div>
                                    <form action="{{ route('drives.roles.remove-assignment', [$drive, $assignment]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this role assignment?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No assignments yet. Assign this role to people or users from their respective pages.</p>
                @endif
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="dashboard-card">
                <h5 class="mb-3">Quick Actions</h5>
                <a href="{{ route('drives.roles.edit', [$drive, $role]) }}" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-edit me-2"></i>Edit Role
                </a>
                <form action="{{ route('drives.roles.destroy', [$drive, $role]) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('Are you sure you want to delete this role? All assignments will be removed.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-trash me-2"></i>Delete Role
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

