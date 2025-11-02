@extends('layouts.dashboard')

@section('title', 'Projects - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('drives.show', $drive) }}">{{ $drive->name }}</a></li>
                            <li class="breadcrumb-item active">Projects</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">Projects</h1>
                    <p class="text-muted">Manage your projects and tasks</p>
                </div>
                <div class="d-flex gap-2">
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.projects.projects.create', $drive) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>New Project
                        </a>
                    @endif
                    <a href="{{ route('drives.projects.task-labels.index', $drive) }}" class="btn btn-outline-primary">
                        <i class="fas fa-tags me-2"></i>Labels
                    </a>
                    <a href="{{ route('drives.show', $drive) }}" class="btn btn-outline-secondary">
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
        @forelse($projects as $project)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="dashboard-card h-100">
                    @if($project->header_image_path)
                        <div class="project-header-image mb-3" style="background-image: url('{{ asset('storage/' . $project->header_image_path) }}'); height: 120px; background-size: cover; background-position: center; border-radius: 8px;"></div>
                    @else
                        <div class="project-header-color mb-3" style="background-color: {{ $project->color }}; height: 120px; border-radius: 8px; opacity: 0.2;"></div>
                    @endif
                    
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="mb-0">
                            <a href="{{ route('drives.projects.projects.show', [$drive, $project]) }}" class="text-decoration-none">
                                {{ $project->name }}
                            </a>
                        </h5>
                        <span class="badge bg-{{ $project->status === 'active' ? 'success' : ($project->status === 'archived' ? 'secondary' : 'info') }}">
                            {{ ucfirst($project->status) }}
                        </span>
                    </div>
                    
                    @if($project->description)
                        <p class="text-muted small mb-3">{{ Str::limit($project->description, 100) }}</p>
                    @endif

                    <div class="d-flex justify-content-between align-items-center text-muted small mb-3">
                        <div>
                            <i class="fas fa-tasks me-1"></i>
                            {{ $project->completed_tasks ?? 0 }}/{{ $project->total_tasks ?? 0 }} tasks
                        </div>
                        @if($project->is_public)
                            <span class="badge bg-info">
                                <i class="fas fa-globe me-1"></i>Public
                            </span>
                        @endif
                    </div>

                    @if($project->start_date || $project->end_date)
                        <div class="text-muted small mb-3">
                            @if($project->start_date)
                                <i class="fas fa-calendar-alt me-1"></i>
                                {{ $project->start_date->format('M d, Y') }}
                            @endif
                            @if($project->end_date)
                                <span class="ms-2">→ {{ $project->end_date->format('M d, Y') }}</span>
                            @endif
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        <a href="{{ route('drives.projects.projects.show', [$drive, $project]) }}" class="btn btn-sm btn-primary flex-fill">
                            <i class="fas fa-eye me-1"></i>View
                        </a>
                        @if($drive->canEdit(auth()->user()))
                            <a href="{{ route('drives.projects.projects.edit', [$drive, $project]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('drives.projects.projects.destroy', [$drive, $project]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this project?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="dashboard-card text-center py-5">
                    <i class="fas fa-project-diagram fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No projects yet</h4>
                    <p class="text-muted">Create your first project to get started</p>
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.projects.projects.create', $drive) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create Project
                        </a>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($projects->hasPages())
        <div class="row">
            <div class="col-12">
                {{ $projects->links() }}
            </div>
        </div>
    @endif
</div>
@endsection

