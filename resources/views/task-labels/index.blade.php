@extends('layouts.dashboard')

@section('title', 'Task Labels - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('drives.projects.projects.index', $drive) }}">Projects</a></li>
                            <li class="breadcrumb-item active">Task Labels</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">Task Labels</h1>
                    <p class="text-muted">Manage labels for categorizing tasks</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('drives.projects.task-labels.create', $drive) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Label
                    </a>
                    <a href="{{ route('drives.projects.projects.index', $drive) }}" class="btn btn-outline-secondary">
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

    <!-- Labels Grid -->
    <div class="row">
        @forelse($labels as $label)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge" style="background-color: {{ $label->color }}; width: 30px; height: 30px; display: inline-block; border-radius: 4px;"></span>
                            <h5 class="mb-0">{{ $label->name }}</h5>
                        </div>
                        @if(!$label->is_active)
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </div>
                    
                    @if($label->description)
                        <p class="text-muted small mb-3">{{ $label->description }}</p>
                    @endif

                    <div class="d-flex gap-2">
                        <a href="{{ route('drives.projects.task-labels.show', [$drive, $label]) }}" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="fas fa-eye me-1"></i>View
                        </a>
                        <a href="{{ route('drives.projects.task-labels.edit', [$drive, $label]) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('drives.projects.task-labels.destroy', [$drive, $label]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this label?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="dashboard-card text-center py-5">
                    <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No labels yet</h4>
                    <p class="text-muted">Create labels to organize your tasks</p>
                    <a href="{{ route('drives.projects.task-labels.create', $drive) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Label
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($labels->hasPages())
        <div class="row">
            <div class="col-12">
                {{ $labels->links() }}
            </div>
        </div>
    @endif
</div>
@endsection

