@extends('layouts.homepage')

@section('title', $project->name . ' - Public Board')

@push('styles')
<style>
    /* Public Board Styles */
    .public-board-section {
        padding: 6rem 0 4rem;
        min-height: 100vh;
    }
    
    .public-board-header {
        background: linear-gradient(135deg, {{ $project->color }} 0%, {{ $project->color }}dd 100%);
        color: white;
        padding: 4rem 0;
        margin-bottom: 3rem;
        border-radius: 1rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }
    
    .public-board-header h1 {
        color: white;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .public-board-header p {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.1rem;
    }
    
    .public-board-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1rem;
    }
    
    .public-kanban-board {
        overflow-x: auto;
        padding-bottom: 2rem;
        scroll-snap-type: x proximity;
    }
    
    .public-kanban-columns {
        width: max-content;
        min-height: 100%;
    }
    
    .public-kanban-column-wrapper {
        flex: 0 0 320px;
        width: 320px;
        scroll-snap-align: start;
    }
    
    .public-kanban-column {
        min-height: 500px;
        background-color: var(--bg-secondary);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border: 2px solid var(--border-color);
        display: flex;
        flex-direction: column;
    }
    
    @media (max-width: 768px) {
        .public-kanban-column-wrapper {
            flex-basis: 280px;
            width: 280px;
        }
    }
    
    .public-kanban-column-header {
        flex-shrink: 0;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        border-bottom: 2px solid var(--border-color);
    }
    
    .public-kanban-column-header h6 {
        color: var(--text-color);
        font-weight: 600;
        margin: 0;
    }
    
    .public-kanban-column-content {
        flex: 1;
        overflow-y: auto;
        padding-right: 0.5rem;
    }
    
    /* Custom scrollbar for public kanban columns */
    .public-kanban-column-content::-webkit-scrollbar {
        width: 6px;
    }
    
    .public-kanban-column-content::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .public-kanban-column-content::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 3px;
    }
    
    .public-task-card {
        background-color: var(--bg-primary);
        border: 2px solid var(--border-color);
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        transition: all 0.2s ease;
        position: relative;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .public-task-card:hover {
        border-color: var(--brand-teal, #31d8b2);
        box-shadow: 0 4px 12px rgba(49, 216, 178, 0.2);
        transform: translateY(-2px);
    }
    
    .public-task-card-title {
        font-weight: 600;
        font-size: 0.95rem;
        line-height: 1.4;
        margin-bottom: 0.5rem;
        color: var(--text-color);
    }
    
    .public-task-card-description {
        font-size: 0.85rem;
        color: var(--text-color);
        opacity: 0.7;
        margin-bottom: 0.75rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .public-task-card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }
    
    .public-task-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-color);
    }
    
    .public-task-card-header-image {
        width: 100%;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        margin-bottom: 0.75rem;
        border: 1px solid var(--border-color);
    }
    
    .public-empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--text-color);
        opacity: 0.5;
    }
    
    .public-empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
    }
    
    /* CSS Variables for theme support */
    :root {
        --bg-primary: #1e1e28;
        --bg-secondary: #2a2a3a;
        --border-color: rgba(255, 255, 255, 0.1);
        --text-color: rgba(255, 255, 255, 0.9);
    }
    
    [data-theme="light"] {
        --bg-primary: #ffffff;
        --bg-secondary: #f8f9fa;
        --border-color: rgba(0, 0, 0, 0.1);
        --text-color: #1e1e28;
    }
    
    [data-theme="light"] .public-board-header {
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }
    
    [data-theme="light"] .public-kanban-column {
        background-color: #ffffff;
        border-color: rgba(0, 0, 0, 0.1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    [data-theme="light"] .public-task-card {
        background-color: #f8f9fa;
        border-color: rgba(0, 0, 0, 0.15);
    }
    
    [data-theme="light"] .public-task-card:hover {
        border-color: #31d8b2;
        box-shadow: 0 4px 12px rgba(49, 216, 178, 0.15);
    }
    
    /* Task Sidebar for Public View */
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
<section class="public-board-section">
    <div class="public-board-container">
        <!-- Public Board Header -->
        <div class="public-board-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-12">
                        @if($project->header_image_path)
                            <img src="{{ asset('storage/' . $project->header_image_path) }}" 
                                 alt="{{ $project->name }}" 
                                 class="img-fluid rounded mb-4"
                                 style="max-height: 300px; width: 100%; object-fit: cover; border: 3px solid rgba(255, 255, 255, 0.2);">
                        @endif
                        <h1>{{ $project->name }}</h1>
                        @if($project->description)
                            <p class="mb-0">{{ $project->description }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanban Board -->
        <div class="public-kanban-board">
            <div class="public-kanban-columns d-flex gap-3 flex-nowrap">
                @foreach($statuses as $status)
                    <div class="public-kanban-column-wrapper">
                        <div class="public-kanban-column">
                            <div class="public-kanban-column-header d-flex justify-content-between align-items-center">
                                <h6 class="d-flex align-items-center gap-2 mb-0">
                                    <span class="badge" style="background-color: {{ $status->color }};">{{ $status->tasks->count() }}</span>
                                    <span>{{ $status->name }}</span>
                                </h6>
                                @if($status->is_completed)
                                    <span class="badge bg-success">Completed</span>
                                @endif
                            </div>
                            <div class="public-kanban-column-content">
                                @forelse($status->tasks as $task)
                                    @include('projects.partials.public-task-card', ['task' => $task, 'status' => $status])
                                @empty
                                    <div class="public-empty-state">
                                        <i class="fas fa-layer-group"></i>
                                        <p class="small mb-0">No tasks</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- Task Sidebar -->
    <div class="task-sidebar-overlay" id="taskSidebarOverlay"></div>
    <div class="task-sidebar" id="taskSidebar">
        <div class="task-sidebar-header">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h5 class="mb-0" id="taskSidebarTitle" style="color: var(--text-color);">Task Details</h5>
                <button type="button" class="btn btn-sm btn-link text-muted" id="taskSidebarClose" style="padding: 0.25rem 0.5rem;">
                    <i class="fas fa-times fa-lg"></i>
                </button>
            </div>
        </div>
        <div class="task-sidebar-content" id="taskSidebarContent">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Task sidebar functionality for public view
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('taskSidebar');
        const overlay = document.getElementById('taskSidebarOverlay');
        const closeBtn = document.getElementById('taskSidebarClose');
        
        @php
            $publicTaskData = $project->tasks
                ->whereNull('deleted_at')
                ->mapWithKeys(function ($task) {
                    $status = $task->status ? [
                        'id' => $task->status->id,
                        'slug' => $task->status->slug,
                        'name' => $task->status->name,
                        'color' => $task->status->color,
                        'is_completed' => (bool) $task->status->is_completed,
                    ] : null;

                    return [
                        $task->id => [
                            'id' => $task->id,
                            'title' => $task->title,
                            'description' => $task->description ?? '',
                            'status' => $status,
                            'priority' => $task->priority,
                            'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                            'owner' => $task->owner ? $task->owner->name : null,
                            'members' => $task->members->pluck('name')->toArray(),
                            'labels' => $task->labels->map(function ($label) {
                                return [
                                    'name' => $label->name,
                                    'color' => $label->color,
                                ];
                            })->toArray(),
                            'created_at' => $task->created_at->format('M d, Y'),
                            'is_overdue' => (bool) $task->isOverdue(),
                        ],
                    ];
                })
                ->toArray();
        @endphp

        const taskData = @json($publicTaskData);
        
        window.openTaskSidebar = function(taskId) {
            const task = taskData[taskId];
            if (!task) return;
            
            // Update sidebar title
            document.getElementById('taskSidebarTitle').textContent = task.title;
            
            // Build sidebar content
            let html = '';
            
            // Status and Priority
            html += '<div class="task-sidebar-section">';
            html += '<div class="d-flex gap-2 mb-3">';
            if (task.status && task.status.name) {
                html += `<span class="badge fs-6" style="background-color: ${task.status.color}; color: #fff;">`;
                html += `${escapeHtml(task.status.name)}</span>`;
            }
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
        
        // Handle task card clicks
        document.querySelectorAll('.public-task-card').forEach(card => {
            card.style.cursor = 'pointer';
            card.addEventListener('click', function(e) {
                const taskId = parseInt(this.dataset.taskId);
                if (taskId) {
                    openTaskSidebar(taskId);
                }
            });
        });
    });
</script>
@endpush

