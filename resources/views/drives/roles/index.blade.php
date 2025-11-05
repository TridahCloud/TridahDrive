@extends('layouts.dashboard')

@section('title', 'Roles & Permissions - ' . $drive->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('drives.index') }}">Drives</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('drives.show', $drive) }}">{{ $drive->name }}</a></li>
                            <li class="breadcrumb-item active">Roles & Permissions</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-user-shield me-2"></i>Roles & Permissions
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div>
                    <a href="{{ route('drives.roles.create', $drive) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Role
                    </a>
                    <a href="{{ route('drives.show', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($drive->parentDrive)
        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            This is a sub-drive. Roles can inherit from parent drive: <strong>{{ $drive->parentDrive->name }}</strong>
        </div>
    @endif

    <!-- Roles List -->
    <div class="dashboard-card mb-4">
        <h4 class="mb-3 brand-teal">
            <i class="fas fa-users-cog me-2"></i>Drive Roles
        </h4>
        
        @forelse($roles as $role)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <h5 class="mb-0">{{ $role->name }}</h5>
                                @if($role->is_inherited)
                                    <span class="badge bg-info">Inherited</span>
                                @endif
                                @if($role->parentRole)
                                    <span class="badge bg-secondary">From: {{ $role->parentRole->name }}</span>
                                @endif
                            </div>
                            
                            @if($role->description)
                                <p class="text-muted mb-2">{{ $role->description }}</p>
                            @endif
                            
                            <div class="mb-2">
                                <strong>Permissions:</strong>
                                @php
                                    $effectivePermissions = $role->getEffectivePermissions();
                                @endphp
                                @if(count($effectivePermissions) > 0)
                                    <div class="mt-2">
                                        @foreach($effectivePermissions as $key => $value)
                                            <span class="badge bg-success me-1 mb-1">
                                                {{ str_replace(['.', '_'], [' ', ' '], $key) }}
                                                @if(is_array($value))
                                                    ({{ count($value) }} items)
                                                @elseif($value === true)
                                                    ✓
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">No permissions assigned</span>
                                @endif
                            </div>
                            
                            <div class="mb-2">
                                <strong>Assignments:</strong>
                                <span class="text-muted">{{ $role->assignments()->count() }} person(s)/user(s)</span>
                            </div>
                        </div>
                        
                        <div class="ms-3">
                            <a href="{{ route('drives.roles.show', [$drive, $role]) }}" class="btn btn-outline-primary btn-sm mb-2 d-block">
                                <i class="fas fa-eye me-1"></i>View Details
                            </a>
                            <a href="{{ route('drives.roles.edit', [$drive, $role]) }}" class="btn btn-outline-secondary btn-sm mb-2 d-block">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                            <form action="{{ route('drives.roles.destroy', [$drive, $role]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this role? All assignments will be removed.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No roles created yet. 
                @if($drive->parentDrive && $parentRoles->count() > 0)
                    You can inherit roles from the parent drive or create new ones.
                @else
                    Create your first role to start managing permissions.
                @endif
            </div>
        @endforelse
        
        @if($drive->parentDrive && $parentRoles->count() > 0)
            <div class="mt-4">
                <h5 class="mb-3">Available Parent Roles</h5>
                @foreach($parentRoles as $parentRole)
                    <div class="card mb-2">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $parentRole->name }}</strong>
                                    @if($parentRole->description)
                                        <span class="text-muted ms-2">{{ $parentRole->description }}</span>
                                    @endif
                                </div>
                                <span class="badge bg-secondary">Parent Drive</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

