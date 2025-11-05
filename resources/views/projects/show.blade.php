@extends('layouts.dashboard')

@section('title', $project->name . ' - ' . $drive->name)

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.css">
<style>
    .kanban-board {
        height: calc(100vh - 320px);
        min-height: 600px;
        overflow-x: auto;
        overflow-y: hidden;
    }
    
    .kanban-column {
        height: 100%;
        background-color: var(--bg-secondary);
        border-radius: 12px;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        min-height: 500px;
        max-height: 100%;
        overflow-y: auto;
    }
    
    .kanban-column-header {
        flex-shrink: 0;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--border-color);
    }
    
    .kanban-column-content {
        flex: 1;
        overflow-y: auto;
        padding-right: 0.5rem;
        position: relative;
        min-height: 200px;
    }
    
    .kanban-empty-state {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: calc(100% - 1rem);
        pointer-events: none;
        z-index: 0;
        opacity: 0.5;
    }
    
    .kanban-column-content .task-card {
        position: relative;
        z-index: 1;
        pointer-events: auto;
    }
    
    /* Custom scrollbar for kanban columns */
    .kanban-column-content::-webkit-scrollbar {
        width: 6px;
    }
    
    .kanban-column-content::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .kanban-column-content::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 3px;
    }
    
    .kanban-column-content::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    
    .task-card {
        background-color: var(--bg-primary);
        border: 2px solid var(--border-color);
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .task-card:hover {
        border-color: var(--brand-teal, #31d8b2);
        box-shadow: 0 4px 12px rgba(49, 216, 178, 0.2);
        transform: translateY(-2px);
    }
    
    .task-card.dragging {
        opacity: 0.5;
        cursor: grabbing;
    }
    
    .task-card.active {
        border-color: var(--brand-teal, #31d8b2);
        box-shadow: 0 0 0 3px rgba(49, 216, 178, 0.2);
    }
    
    .task-card-header-image {
        width: 100%;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        margin-bottom: 0.75rem;
        border: 1px solid var(--border-color);
    }
    
    .task-card-title {
        font-weight: 600;
        font-size: 0.95rem;
        line-height: 1.4;
        margin-bottom: 0.5rem;
        color: var(--text-color);
    }
    
    .task-card-description {
        font-size: 0.85rem;
        color: var(--text-color);
        opacity: 0.7;
        margin-bottom: 0.75rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .task-card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }
    
    .task-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-color);
    }
    
    .task-card-actions {
        display: none; /* Hide action buttons on card, will show in sidebar */
    }
    
    /* Task Sidebar */
    .task-sidebar {
        position: fixed;
        top: 0;
        right: -450px;
        width: 450px;
        height: 100vh;
        background-color: var(--bg-secondary);
        box-shadow: -4px 0 20px rgba(0, 0, 0, 0.3);
        z-index: 1050;
        transition: right 0.3s ease;
        overflow-y: auto;
        border-left: 2px solid var(--border-color);
    }
    
    .task-sidebar.active {
        right: 0;
    }
    
    .task-sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1049;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    
    .task-sidebar-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    
    .task-sidebar-header {
        padding: 1.5rem;
        border-bottom: 2px solid var(--border-color);
        background-color: var(--bg-primary);
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .task-sidebar-content {
        padding: 1.5rem;
    }
    
    .task-sidebar-section {
        margin-bottom: 2rem;
    }
    
    .task-sidebar-section-title {
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-color);
        opacity: 0.7;
        margin-bottom: 1rem;
    }
    
    .view-switcher .btn {
        border-radius: 0;
    }
    .view-switcher .btn:first-child {
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }
    .view-switcher .btn:last-child {
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }
    
    [data-theme="light"] .task-card {
        border-color: rgba(0, 0, 0, 0.15);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    [data-theme="light"] .task-card:hover {
        border-color: #31d8b2;
        box-shadow: 0 4px 12px rgba(49, 216, 178, 0.15);
    }
    
    [data-theme="light"] .kanban-column {
        background-color: #f8f9fa;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    [data-theme="light"] .kanban-column-header {
        border-bottom-color: rgba(0, 0, 0, 0.1);
    }
    
    [data-theme="light"] .task-sidebar {
        background-color: #ffffff;
        border-left-color: rgba(0, 0, 0, 0.1);
    }
    
    [data-theme="light"] .task-sidebar-header {
        background-color: #f8f9fa;
        border-bottom-color: rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('drives.index') }}">Drives</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('drives.show', $drive) }}">{{ $drive->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('drives.projects.projects.index', $drive) }}">Projects</a></li>
                    <li class="breadcrumb-item active">{{ $project->name }}</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1 class="display-6 mb-0 brand-teal">{{ $project->name }}</h1>
                    @if($project->description)
                        <p class="text-muted">{{ $project->description }}</p>
                    @endif
                    @if($project->is_public && $project->public_key)
                        <div class="mt-2">
                            <div class="input-group" style="max-width: 600px;">
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       id="publicLink" 
                                       value="{{ route('projects.public.show', $project->public_key) }}" 
                                       readonly
                                       style="font-size: 0.875rem;">
                                <button class="btn btn-outline-secondary btn-sm" 
                                        type="button" 
                                        id="copyPublicLinkBtn"
                                        onclick="copyPublicLink()"
                                        title="Copy public link">
                                    <i class="fas fa-copy me-1"></i>Copy Link
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-globe me-1"></i>This project is publicly accessible via the link above
                            </small>
                        </div>
                    @else
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-lock me-1"></i>This project is private
                            </small>
                        </div>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.projects.projects.edit', [$drive, $project]) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-2"></i>Edit Project
                        </a>
                    @endif
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

    <!-- View Switcher -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="view-switcher btn-group" role="group">
                        <a href="{{ route('drives.projects.projects.show', [$drive, $project, 'view' => 'list']) }}" 
                           class="btn btn-{{ $view === 'list' ? 'primary' : 'outline-primary' }}">
                            <i class="fas fa-list me-2"></i>List
                        </a>
                        <a href="{{ route('drives.projects.projects.show', [$drive, $project, 'view' => 'kanban']) }}" 
                           class="btn btn-{{ $view === 'kanban' ? 'primary' : 'outline-primary' }}">
                            <i class="fas fa-columns me-2"></i>Kanban
                        </a>
                        <a href="{{ route('drives.projects.projects.show', [$drive, $project, 'view' => 'gantt']) }}" 
                           class="btn btn-{{ $view === 'gantt' ? 'primary' : 'outline-primary' }}">
                            <i class="fas fa-chart-bar me-2"></i>Gantt
                        </a>
                        <a href="{{ route('drives.projects.projects.show', [$drive, $project, 'view' => 'calendar']) }}" 
                           class="btn btn-{{ $view === 'calendar' ? 'primary' : 'outline-primary' }}">
                            <i class="fas fa-calendar-alt me-2"></i>Calendar
                        </a>
                        <a href="{{ route('drives.projects.projects.show', [$drive, $project, 'view' => 'workload']) }}" 
                           class="btn btn-{{ $view === 'workload' ? 'primary' : 'outline-primary' }}">
                            <i class="fas fa-chart-pie me-2"></i>Workload
                        </a>
                    </div>
                    <div class="d-flex gap-2">
                        @if($drive->canEdit(auth()->user()))
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createLabelModal">
                                <i class="fas fa-tag me-2"></i>Create Label
                            </button>
                            <a href="{{ route('drives.projects.projects.tasks.create', [$drive, $project]) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>New Task
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned People Section -->
    @if($drive->canEdit(auth()->user()) && isset($availablePeople))
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2 brand-teal"></i>Assigned People
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignPeopleModal">
                        <i class="fas fa-user-plus me-1"></i>Assign People
                    </button>
                </div>
                
                @if($project->people->count() > 0)
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($project->people as $person)
                            <div class="badge bg-primary p-2 d-flex align-items-center gap-2">
                                <i class="fas fa-user"></i>
                                <span>{{ $person->full_name }}</span>
                                @if($person->job_title)
                                    <small class="opacity-75">({{ $person->job_title }})</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i>No people assigned to this project yet.
                    </p>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Assign People Modal -->
    @if($drive->canEdit(auth()->user()) && isset($availablePeople))
    <div class="modal fade" id="assignPeopleModal" tabindex="-1" aria-labelledby="assignPeopleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignPeopleModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Assign People to Project
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('drives.projects.projects.assign-people', [$drive, $project]) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Select people from your Drive to assign to this project.</p>
                        
                        @if($availablePeople->count() > 0)
                            <div class="list-group">
                                @foreach($availablePeople as $person)
                                    <label class="list-group-item list-group-item-action">
                                        <div class="d-flex align-items-center">
                                            <input type="checkbox" 
                                                   class="form-check-input me-3" 
                                                   name="person_ids[]" 
                                                   value="{{ $person->id }}"
                                                   {{ $project->people->contains($person->id) ? 'checked' : '' }}>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold">{{ $person->full_name }}</div>
                                                @if($person->job_title)
                                                    <small class="text-muted">{{ $person->job_title }}</small>
                                                @endif
                                                @if($person->type)
                                                    <span class="badge bg-info ms-2">{{ ucfirst($person->type) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                No active people available in this Drive. 
                                <a href="{{ route('drives.people-manager.people.create', $drive) }}" class="alert-link">Add people</a> to assign them to projects.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        @if($availablePeople->count() > 0)
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Assignments
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Create Label Modal -->
    <div class="modal fade" id="createLabelModal" tabindex="-1" aria-labelledby="createLabelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createLabelModalLabel">
                        <i class="fas fa-tag me-2"></i>Create Task Label
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createLabelForm" action="{{ route('drives.projects.task-labels.store', $drive) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show">
                                <strong>Please fix the following errors:</strong>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="label_name" class="form-label">Label Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="label_name" name="name" value="{{ old('name') }}" required placeholder="e.g., Bug, Feature, Urgent">
                        </div>

                        <div class="mb-3">
                            <label for="label_description" class="form-label">Description</label>
                            <textarea class="form-control" id="label_description" name="description" rows="2" placeholder="Optional description">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="label_color" class="form-label">Color</label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="color" class="form-control form-control-color" id="label_color" name="color" value="{{ old('color', '#6366F1') }}" title="Choose color" style="width: 80px; height: 40px;">
                                <div class="flex-grow-1">
                                    <small class="text-muted">Select a color for this label. It will be displayed on task cards.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Label
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- List View -->
    @if($view === 'list')
        @include('projects.views.list')
    @endif

    <!-- Kanban View -->
    @if($view === 'kanban')
        @include('projects.views.kanban')
    @endif

    <!-- Gantt View -->
    @if($view === 'gantt')
        @include('projects.views.gantt')
    @endif

    <!-- Calendar View -->
    @if($view === 'calendar')
        @include('projects.views.calendar')
    @endif

    <!-- Workload View -->
    @if($view === 'workload')
        @include('projects.views.workload')
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    // Kanban drag and drop
    @if($view === 'kanban')
    document.addEventListener('DOMContentLoaded', function() {
        const columns = ['todo', 'in_progress', 'review', 'done', 'blocked'];
        
        columns.forEach(status => {
            const element = document.getElementById('kanban-' + status);
            if (element) {
                new Sortable(element, {
                    group: 'tasks',
                    animation: 150,
                    handle: '.task-card',
                    draggable: '.task-card',
                    ghostClass: 'dragging',
                    onStart: function(evt) {
                        evt.item.classList.add('dragging');
                    },
                    onEnd: function(evt) {
                        evt.item.classList.remove('dragging');
                        const taskId = evt.item.dataset.taskId;
                        const newStatus = evt.to.dataset.status;
                        const oldStatus = evt.from.dataset.status;
                        const sortOrder = Array.from(evt.to.children).indexOf(evt.item);
                        
                        // Update empty states
                        updateEmptyStates(oldStatus, newStatus);
                        
                        fetch('{{ route("drives.projects.projects.tasks.update-status", [$drive, $project, ":task"]) }}'.replace(':task', taskId), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                status: newStatus,
                                sort_order: sortOrder
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Task moved successfully');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            location.reload(); // Reload on error
                        });
                    }
                });
            }
        });
        
        // Function to update empty states when cards are moved
        function updateEmptyStates(oldStatus, newStatus) {
            // Hide empty state for destination column if it has cards now
            const newColumn = document.getElementById('kanban-' + newStatus);
            if (newColumn) {
                const newEmptyState = document.getElementById('empty-' + newStatus);
                const newColumnCards = newColumn.querySelectorAll('.task-card:not(.dragging)');
                if (newColumnCards.length > 0 && newEmptyState) {
                    newEmptyState.style.display = 'none';
                }
            }
            
            // Show empty state for source column if it's now empty
            const oldColumn = document.getElementById('kanban-' + oldStatus);
            if (oldColumn) {
                const oldEmptyState = document.getElementById('empty-' + oldStatus);
                const oldColumnCards = oldColumn.querySelectorAll('.task-card:not(.dragging)');
                if (oldColumnCards.length === 0 && oldEmptyState) {
                    oldEmptyState.style.display = 'block';
                }
            }
        }
        
        // Initialize empty states visibility
        function initializeEmptyStates() {
            const statuses = ['todo', 'in_progress', 'review', 'done', 'blocked'];
            statuses.forEach(status => {
                const column = document.getElementById('kanban-' + status);
                const emptyState = document.getElementById('empty-' + status);
                if (column && emptyState) {
                    const cards = column.querySelectorAll('.task-card');
                    emptyState.style.display = cards.length === 0 ? 'block' : 'none';
                }
            });
        }
        
        // Initialize on page load
        initializeEmptyStates();
        
        // Task sidebar functionality
        const sidebar = document.getElementById('taskSidebar');
        const overlay = document.getElementById('taskSidebarOverlay');
        const closeBtn = document.getElementById('taskSidebarClose');
        const fullViewBtn = document.getElementById('taskSidebarFullView');
        const editBtn = document.getElementById('taskSidebarEdit');
        
        // Task data storage
        const taskData = {
            @foreach($project->tasks->whereNull('deleted_at') as $task)
            @php
                $taskUrl = route('drives.projects.projects.tasks.show', [$drive, $project, $task]);
                $taskEditUrl = route('drives.projects.projects.tasks.edit', [$drive, $project, $task]);
            @endphp
            {{ $task->id }}: {
                id: {{ $task->id }},
                title: @json($task->title),
                description: @json($task->description ?? ''),
                status: @json($task->status),
                priority: @json($task->priority),
                due_date: @json($task->due_date ? $task->due_date->format('Y-m-d') : null),
                owner: @json($task->owner ? $task->owner->name : null),
                members: @json($task->members->map(fn($m) => $m->name)->toArray()),
                labels: @json($task->labels->map(fn($l) => ['name' => $l->name, 'color' => $l->color])->toArray()),
                url: @json($taskUrl),
                edit_url: @json($taskEditUrl),
                created_at: @json($task->created_at->format('M d, Y')),
                is_overdue: {{ $task->isOverdue() ? 'true' : 'false' }},
            },
            @endforeach
        };
        
        window.openTaskSidebar = function(taskId) {
            const task = taskData[taskId];
            if (!task) return;
            
            // Update sidebar title
            document.getElementById('taskSidebarTitle').textContent = task.title;
            
            // Update links
            fullViewBtn.href = task.url;
            editBtn.href = task.edit_url;
            
            // Build sidebar content
            let html = '';
            
            // Status and Priority
            html += '<div class="task-sidebar-section">';
            html += '<div class="d-flex gap-2 mb-3">';
            html += `<span class="badge bg-${task.status === 'done' ? 'success' : (task.status === 'todo' ? 'secondary' : (task.status === 'blocked' ? 'danger' : 'primary'))} fs-6">`;
            html += `${task.status.charAt(0).toUpperCase() + task.status.slice(1).replace('_', ' ')}</span>`;
            html += `<span class="badge bg-${task.priority === 'urgent' ? 'danger' : (task.priority === 'high' ? 'warning' : (task.priority === 'medium' ? 'info' : 'secondary'))} fs-6">`;
            html += `${task.priority.charAt(0).toUpperCase() + task.priority.slice(1)}</span>`;
            html += '</div>';
            html += '</div>';
            
            // Description
            if (task.description) {
                html += '<div class="task-sidebar-section">';
                html += '<div class="task-sidebar-section-title">Description</div>';
                html += `<p style="color: var(--text-color); white-space: pre-wrap;">${escapeHtml(task.description)}</p>`;
                html += '</div>';
            }
            
            // Due Date
            if (task.due_date) {
                html += '<div class="task-sidebar-section">';
                html += '<div class="task-sidebar-section-title">Due Date</div>';
                const dueDate = new Date(task.due_date + 'T00:00:00');
                const formattedDate = dueDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                html += `<p style="color: var(--text-color);">${formattedDate}${task.is_overdue ? ' <span class="badge bg-danger">Overdue</span>' : ''}</p>`;
                html += '</div>';
            }
            
            // Owner
            if (task.owner) {
                html += '<div class="task-sidebar-section">';
                html += '<div class="task-sidebar-section-title">Owner</div>';
                html += `<p style="color: var(--text-color);">${escapeHtml(task.owner)}</p>`;
                html += '</div>';
            }
            
            // Members
            if (task.members.length > 0) {
                html += '<div class="task-sidebar-section">';
                html += '<div class="task-sidebar-section-title">Assigned Members</div>';
                html += '<div class="d-flex flex-wrap gap-1">';
                task.members.forEach(member => {
                    html += `<span class="badge bg-secondary">${escapeHtml(member)}</span>`;
                });
                html += '</div>';
                html += '</div>';
            }
            
            // Labels
            if (task.labels.length > 0) {
                html += '<div class="task-sidebar-section">';
                html += '<div class="task-sidebar-section-title">Labels</div>';
                html += '<div class="d-flex flex-wrap gap-1">';
                task.labels.forEach(label => {
                    html += `<span class="badge" style="background-color: ${label.color}; color: white;">${escapeHtml(label.name)}</span>`;
                });
                html += '</div>';
                html += '</div>';
            }
            
            // Created Date
            html += '<div class="task-sidebar-section">';
            html += '<div class="task-sidebar-section-title">Created</div>';
            html += `<p class="text-muted small">${escapeHtml(task.created_at)}</p>`;
            html += '</div>';
            
            document.getElementById('taskSidebarContent').innerHTML = html;
            
            // Show sidebar
            sidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        };
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function closeTaskSidebar() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        closeBtn.addEventListener('click', closeTaskSidebar);
        overlay.addEventListener('click', closeTaskSidebar);
        
        // Prevent sidebar from closing when clicking inside it
        sidebar.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        // Close sidebar on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                closeTaskSidebar();
            }
        });
        
        // Handle task card clicks (but not during drag)
        let isDragging = false;
        let dragStartTime = 0;
        
        document.querySelectorAll('.task-card').forEach(card => {
            card.addEventListener('mousedown', function(e) {
                dragStartTime = Date.now();
                isDragging = false;
            });
            
            card.addEventListener('dragstart', function(e) {
                isDragging = true;
                this.classList.add('dragging');
            });
            
            card.addEventListener('dragend', function(e) {
                this.classList.remove('dragging');
                setTimeout(() => {
                    isDragging = false;
                }, 100);
            });
            
            card.addEventListener('click', function(e) {
                // Only open sidebar if it wasn't a drag operation
                const timeSinceDragStart = Date.now() - dragStartTime;
                if (!isDragging && timeSinceDragStart < 300) {
                    const taskId = parseInt(this.dataset.taskId);
                    openTaskSidebar(taskId);
                }
            });
        });
        
        // Handle label creation form submission
        const createLabelForm = document.getElementById('createLabelForm');
        if (createLabelForm) {
            createLabelForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    }
                    return response.json().then(err => Promise.reject(err));
                })
                .then(data => {
                    if (data.success) {
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('createLabelModal'));
                        if (modal) {
                            modal.hide();
                        }
                        
                        // Show success message
                        if (typeof showToast === 'function') {
                            showToast('success', data.message || 'Label created successfully!');
                        }
                        
                        // Reload page to show new label
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Handle validation errors
                    if (error.errors) {
                        let errorHtml = '<div class="alert alert-danger alert-dismissible fade show"><strong>Please fix the following errors:</strong><ul class="mb-0">';
                        for (const [key, messages] of Object.entries(error.errors)) {
                            messages.forEach(message => {
                                errorHtml += `<li>${message}</li>`;
                            });
                        }
                        errorHtml += '</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                        
                        const modalBody = document.querySelector('#createLabelModal .modal-body');
                        if (modalBody) {
                            const existingAlert = modalBody.querySelector('.alert-danger');
                            if (existingAlert) {
                                existingAlert.remove();
                            }
                            modalBody.insertAdjacentHTML('afterbegin', errorHtml);
                        }
                    } else {
                        alert(error.message || 'An error occurred while creating the label. Please try again.');
                    }
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });
            
            // Reset form when modal is closed
            const createLabelModal = document.getElementById('createLabelModal');
            if (createLabelModal) {
                createLabelModal.addEventListener('hidden.bs.modal', function() {
                    createLabelForm.reset();
                    const errorAlerts = createLabelForm.querySelectorAll('.alert-danger');
                    errorAlerts.forEach(alert => alert.remove());
                    const submitBtn = createLabelForm.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Create Label';
                    }
                });
            }
        }
    });
    @endif
    
    // Copy public link functionality
    function copyPublicLink() {
        const publicLinkInput = document.getElementById('publicLink');
        const copyBtn = document.getElementById('copyPublicLinkBtn');
        
        if (publicLinkInput && copyBtn) {
            publicLinkInput.select();
            publicLinkInput.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                navigator.clipboard.writeText(publicLinkInput.value).then(function() {
                    // Show success feedback
                    const originalHTML = copyBtn.innerHTML;
                    copyBtn.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
                    copyBtn.classList.remove('btn-outline-secondary');
                    copyBtn.classList.add('btn-success');
                    
                    // Reset after 2 seconds
                    setTimeout(function() {
                        copyBtn.innerHTML = originalHTML;
                        copyBtn.classList.remove('btn-success');
                        copyBtn.classList.add('btn-outline-secondary');
                    }, 2000);
                    
                    // Show toast notification if available
                    if (typeof showToast === 'function') {
                        showToast('success', 'Public link copied to clipboard!');
                    }
                }).catch(function(err) {
                    console.error('Failed to copy: ', err);
                    // Fallback for older browsers
                    document.execCommand('copy');
                    alert('Link copied to clipboard!');
                });
            } catch (err) {
                // Fallback for older browsers
                try {
                    document.execCommand('copy');
                    alert('Link copied to clipboard!');
                } catch (e) {
                    alert('Failed to copy link. Please select and copy manually.');
                }
            }
        }
    }
    
    // Make copy function available globally
    window.copyPublicLink = copyPublicLink;
</script>
@endpush

