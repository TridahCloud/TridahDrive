@extends('layouts.dashboard')

@section('title', 'Shared Projects')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('drives.index') }}">Drives</a></li>
                            <li class="breadcrumb-item active">Shared Projects</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">Shared Projects</h1>
                    <p class="text-muted">Projects that have been shared with you</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('drives.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Projects Grid -->
    <div class="row">
        @forelse($sharedProjects as $project)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="dashboard-card h-100">
                    @if($project->header_image_path)
                        <div class="project-header-image mb-3" style="background-image: url('{{ asset('storage/' . $project->header_image_path) }}'); height: 120px; background-size: cover; background-position: center; border-radius: 8px;"></div>
                    @else
                        <div class="project-header-color mb-3" style="background-color: {{ $project->color }}; height: 120px; border-radius: 8px; opacity: 0.2;"></div>
                    @endif
                    
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="mb-0">
                            <a href="{{ route('drives.projects.projects.show', [$project->drive, $project]) }}" class="text-decoration-none">
                                {{ $project->name }}
                            </a>
                        </h5>
                        <span class="badge bg-{{ $project->status === 'active' ? 'success' : ($project->status === 'archived' ? 'secondary' : 'info') }}">
                            {{ ucfirst($project->status) }}
                        </span>
                    </div>
                    
                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="fas fa-folder me-1"></i>
                            {{ $project->drive->name }}
                        </small>
                    </div>
                    
                    @if($project->description)
                        <p class="text-muted small mb-3">{{ Str::limit($project->description, 100) }}</p>
                    @endif

                    <div class="d-flex justify-content-between align-items-center text-muted small mb-3">
                        <div>
                            <i class="fas fa-tasks me-1"></i>
                            {{ $project->completed_tasks ?? 0 }}/{{ $project->total_tasks ?? 0 }} tasks
                        </div>
                        @php
                            $userRole = $project->getUserRole(auth()->user());
                        @endphp
                        <span class="badge bg-{{ $userRole === 'editor' ? 'primary' : 'secondary' }}">
                            {{ ucfirst($userRole) }}
                        </span>
                    </div>

                    @if($project->start_date || $project->end_date)
                        <div class="text-muted small mb-3">
                            @if($project->start_date)
                                <i class="fas fa-calendar-alt me-1"></i>
                                {{ $project->drive->formatForUser(\Carbon\Carbon::parse($project->start_date), 'M d, Y', auth()->user()) }}
                            @endif
                            @if($project->end_date)
                                <span class="ms-2">→ {{ $project->drive->formatForUser(\Carbon\Carbon::parse($project->end_date), 'M d, Y', auth()->user()) }}</span>
                            @endif
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        <a href="{{ route('drives.projects.projects.show', [$project->drive, $project]) }}" class="btn btn-sm btn-primary flex-fill">
                            <i class="fas fa-eye me-1"></i>View
                        </a>
                        @if($project->userCanEdit(auth()->user()))
                            <a href="{{ route('drives.projects.projects.edit', [$project->drive, $project]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="dashboard-card text-center py-5">
                    <i class="fas fa-share-alt fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No shared projects</h4>
                    <p class="text-muted">Projects that are shared with you will appear here</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

