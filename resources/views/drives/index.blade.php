@extends('layouts.dashboard')

@section('title', 'My Drives')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 mb-0 brand-teal">My Drives</h1>
                <p class="text-muted">Manage your personal and shared drives</p>
            </div>
            <a href="{{ route('drives.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>New Shared Drive
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Personal Drives -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="mb-3">
                <i class="fas fa-user me-2 brand-teal"></i>Personal Drives
            </h3>
            <div class="row">
                @forelse($personalDrives as $drive)
                    <div class="col-md-4 col-lg-3 mb-3">
                        <div class="dashboard-card h-100">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="drive-icon" style="background: {{ $drive->color ?? '#31d8b2' }}; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-{{ $drive->icon ?? 'folder' }} text-white"></i>
                                </div>
                                @if($drive->owner_id === auth()->id())
                                    <span class="badge bg-brand-teal">Owner</span>
                                @endif
                            </div>
                            <h5 class="mb-2">{{ $drive->name }}</h5>
                            <p class="text-muted small mb-3">{{ $drive->description ?? 'No description' }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">
                                    <i class="fas fa-file me-1"></i>{{ $drive->items()->whereNull('deleted_at')->count() }} items
                                </span>
                                <a href="{{ route('drives.show', $drive) }}" class="btn btn-sm btn-primary">
                                    Open <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="dashboard-card text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No personal drives yet</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Shared Drives -->
    <div class="row">
        <div class="col-12">
            <h3 class="mb-3">
                <i class="fas fa-users me-2 brand-blue"></i>Shared Drives
            </h3>
            <div class="row">
                @forelse($sharedDrives as $drive)
                    <div class="col-md-4 col-lg-3 mb-3">
                        <div class="dashboard-card h-100">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="drive-icon" style="background: {{ $drive->color ?? '#204e7e' }}; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-{{ $drive->icon ?? 'folder-open' }} text-white"></i>
                                </div>
                                <span class="badge bg-brand-blue">
                                    {{ ucfirst($drive->getUserRole(auth()->user())) }}
                                </span>
                            </div>
                            <h5 class="mb-2">{{ $drive->name }}</h5>
                            <p class="text-muted small mb-3">{{ $drive->description ?? 'No description' }}</p>
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <span class="text-muted small">
                                    <i class="fas fa-users me-1"></i>{{ $drive->users()->count() }} members
                                </span>
                                @if($drive->owner_id !== auth()->id())
                                    <form action="{{ route('drives.members.leave', $drive) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to leave this drive?')" title="Leave Drive">
                                            <i class="fas fa-sign-out-alt"></i>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('drives.show', $drive) }}" class="btn btn-sm btn-primary flex-grow-1">
                                    Open <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="dashboard-card text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No shared drives yet</p>
                            <a href="{{ route('drives.create') }}" class="btn btn-primary mt-2">
                                Create Shared Drive
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

