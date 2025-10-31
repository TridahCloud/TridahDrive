@extends('layouts.dashboard')

@section('title', $taskLabel->name . ' - Task Label')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('drives.index') }}">Drives</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('drives.show', $drive) }}">{{ $drive->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('drives.projects.task-labels.index', $drive) }}">Task Labels</a></li>
                    <li class="breadcrumb-item active">{{ $taskLabel->name }}</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-6 mb-0 brand-teal">
                        <span class="badge me-3" style="background-color: {{ $taskLabel->color }}; width: 40px; height: 40px; display: inline-block; vertical-align: middle; border-radius: 4px;"></span>
                        {{ $taskLabel->name }}
                    </h1>
                    @if($taskLabel->description)
                        <p class="text-muted">{{ $taskLabel->description }}</p>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('drives.projects.task-labels.edit', [$drive, $taskLabel]) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                    <a href="{{ route('drives.projects.task-labels.index', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="dashboard-card mb-4">
                <h5 class="mb-3">Tasks with this Label</h5>
                @if($taskLabel->tasks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Project</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Due Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($taskLabel->tasks as $task)
                                    <tr>
                                        <td>
                                            <a href="{{ route('drives.projects.projects.tasks.show', [$drive, $task->project, $task]) }}" class="text-decoration-none">
                                                {{ $task->title }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('drives.projects.projects.show', [$drive, $task->project]) }}" class="text-decoration-none">
                                                {{ $task->project->name }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $task->status === 'done' ? 'success' : ($task->status === 'todo' ? 'secondary' : 'primary') }}">
                                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : 'info') }}">
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($task->due_date)
                                                {{ $task->due_date->format('M d, Y') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('drives.projects.projects.tasks.show', [$drive, $task->project, $task]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">No tasks are using this label yet.</p>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-card mb-4">
                <h5 class="mb-3">Label Information</h5>
                
                <div class="mb-3">
                    <label class="text-muted small">Color</label>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge" style="background-color: {{ $taskLabel->color }}; width: 30px; height: 30px; display: inline-block; border-radius: 4px;"></span>
                        <code>{{ $taskLabel->color }}</code>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Status</label>
                    <p class="mb-0">
                        <span class="badge bg-{{ $taskLabel->is_active ? 'success' : 'secondary' }}">
                            {{ $taskLabel->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Tasks Using This Label</label>
                    <p class="mb-0"><strong>{{ $taskLabel->tasks->count() }}</strong></p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Created By</label>
                    <p class="mb-0">{{ $taskLabel->creator->name }}</p>
                    <small class="text-muted">{{ $taskLabel->created_at->format('M d, Y g:i A') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

