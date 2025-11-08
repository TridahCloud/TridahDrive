<div class="row">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="kanban-board-wrapper">
                <div class="kanban-board">
                    <div class="kanban-columns d-flex gap-3 flex-nowrap">
                @foreach($statuses as $status)
                    <div class="kanban-column-wrapper">
                        <div class="dashboard-card d-flex flex-column" style="padding: 0;">
                            <div class="kanban-column-header p-3 d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 d-flex align-items-center gap-2">
                                    <span class="badge" style="background-color: {{ $status->color }};">{{ $status->tasks->count() }}</span>
                                    <span>{{ $status->name }}</span>
                                </h6>
                                @if($status->is_completed)
                                    <span class="badge bg-success fw-normal">Completed</span>
                                @endif
                            </div>
                            <div id="kanban-status-{{ $status->id }}" data-status-id="{{ $status->id }}" class="kanban-column-content">
                                <div class="kanban-empty-state text-center text-muted py-5" id="empty-status-{{ $status->id }}" style="display: {{ $status->tasks->isEmpty() ? 'block' : 'none' }};">
                                    <i class="fas fa-layer-group fa-2x mb-2 opacity-50"></i>
                                    <p class="small mb-0">No tasks</p>
                                </div>
                                @foreach($status->tasks as $task)
                                    @include('projects.partials.task-card', ['task' => $task, 'status' => $status])
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
                    </div>
                </div>
            </div>
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
        <div class="d-flex gap-2">
            <a href="#" id="taskSidebarFullView" class="btn btn-primary btn-sm">
                <i class="fas fa-external-link-alt me-2"></i>Full View
            </a>
            <a href="#" id="taskSidebarEdit" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
        </div>
    </div>
    <div class="task-sidebar-content" id="taskSidebarContent">
        <!-- Content will be loaded dynamically -->
    </div>
</div>

