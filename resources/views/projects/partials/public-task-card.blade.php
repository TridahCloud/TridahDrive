@php
    $headerImage = $task->attachments->where('type', 'header')->first();
    $statusModel = $task->status ?? ($status ?? null);
@endphp

<div class="public-task-card" data-task-id="{{ $task->id }}">
    @if($headerImage)
        <img src="{{ route('drives.projects.projects.tasks.attachments.show', [$task->project->drive, $task->project, $task, $headerImage]) }}" 
             alt="Task header" 
             class="public-task-card-header-image">
    @endif
    
    <div class="public-task-card-title">
        {{ $task->title }}
    </div>
    
    @if($task->description)
        <div class="public-task-card-description">
            {{ Str::limit(strip_tags($task->description), 100) }}
        </div>
    @endif

    <div class="public-task-card-meta">
        <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : ($task->priority === 'medium' ? 'info' : 'secondary')) }}">
            <i class="fas fa-flag me-1"></i>{{ ucfirst($task->priority) }}
        </span>
        @foreach($task->labels->take(3) as $label)
            <span class="badge" style="background-color: {{ $label->color }}; color: white;">
                {{ $label->name }}
            </span>
        @endforeach
        @if($task->labels->count() > 3)
            <span class="badge bg-secondary">+{{ $task->labels->count() - 3 }}</span>
        @endif
    </div>

    <div class="public-task-card-footer">
        <div class="d-flex align-items-center gap-2">
            @if($task->due_date)
                <small class="text-{{ $task->isOverdue() ? 'danger' : 'muted' }}" style="color: var(--text-color); opacity: 0.7;">
                    <i class="fas fa-calendar-alt me-1"></i>
                    {{ $task->due_date->format('M d') }}
                </small>
            @endif
            @if($task->owner)
                <div class="bg-info rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 24px; height: 24px; font-size: 0.7rem;"
                     title="{{ $task->owner->name }}">
                    <span style="color: white;">{{ substr($task->owner->name, 0, 1) }}</span>
                </div>
            @endif
            @if($task->members->count() > 0)
                <small class="text-muted">
                    <i class="fas fa-users me-1"></i>{{ $task->members->count() }}
                </small>
            @endif
        </div>
        <div class="d-flex align-items-center gap-1">
            @if($statusModel)
                <span class="badge task-status-badge" style="background-color: {{ $statusModel->color }}; color: #fff;" data-role="task-status-badge">
                    {{ $statusModel->name }}
                </span>
            @endif
        </div>
    </div>
</div>

