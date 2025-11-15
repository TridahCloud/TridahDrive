<!-- Kanban Filters -->
<div class="row mb-3" id="kanbanFiltersRow" style="display: none;">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleKanbanFilters">
                    <i class="fas fa-filter me-1"></i>Hide Filters
                </button>
                <select class="form-select form-select-sm" style="width: auto;" id="kanbanFilterStatus">
                    <option value="">All Status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->slug }}">{{ $status->name }}</option>
                    @endforeach
                </select>
                <select class="form-select form-select-sm" style="width: auto;" id="kanbanFilterPriority">
                    <option value="">All Priorities</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
                <select class="form-select form-select-sm" style="width: auto;" id="kanbanFilterLabel">
                    <option value="">All Labels</option>
                    @foreach($labels as $label)
                        <option value="{{ $label->id }}">{{ $label->name }}</option>
                    @endforeach
                </select>
                <select class="form-select form-select-sm" style="width: auto;" id="kanbanFilterAssignee">
                    <option value="">All Assignees</option>
                    @foreach($driveMembers as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearKanbanFilters">
                    <i class="fas fa-times me-1"></i>Clear
                </button>
            </div>
        </div>
    </div>
</div>

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
                                @if($drive->canEdit(auth()->user()))
                                <div class="quick-add-task mb-2 p-2" style="display: none;">
                                    <form class="quick-add-form" data-status-id="{{ $status->id }}">
                                        <input type="text" class="form-control form-control-sm" placeholder="+ Add task..." autocomplete="off">
                                        <div class="quick-add-options mt-2" style="display: none;">
                                            <button type="submit" class="btn btn-sm btn-primary">Add</button>
                                            <button type="button" class="btn btn-sm btn-secondary cancel-quick-add">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="quick-add-button mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary w-100 quick-add-trigger" data-status-id="{{ $status->id }}">
                                        <i class="fas fa-plus me-1"></i>Add Task
                                    </button>
                                </div>
                                @endif
                                <div class="kanban-empty-state text-center text-muted py-5" id="empty-status-{{ $status->id }}" style="display: {{ $status->tasks->isEmpty() ? 'block' : 'none' }};">
                                    <i class="fas fa-layer-group fa-2x mb-2 opacity-50"></i>
                                    <p class="small mb-2 fw-semibold">No tasks in {{ $status->name }}</p>
                                    <p class="small mb-3 opacity-75">Drag tasks here or create a new one to get started</p>
                                    @if($drive->canEdit(auth()->user()))
                                    <button type="button" class="btn btn-sm btn-outline-primary quick-add-trigger" data-status-id="{{ $status->id }}" style="pointer-events: auto; position: relative; z-index: 10;">
                                        <i class="fas fa-plus me-1"></i>Add Task
                                    </button>
                                    @endif
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
            @if($drive->canEdit(auth()->user()))
            <a href="#" id="taskSidebarEdit" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            @endif
        </div>
    </div>
    <div class="task-sidebar-content" id="taskSidebarContent">
        <!-- Content will be loaded dynamically -->
    </div>
</div>

<!-- Task Context Menu -->
<div class="task-context-menu" id="taskContextMenu" style="display: none; position: fixed; z-index: 2000;">
    <div class="list-group" style="min-width: 200px;">
        <a href="#" class="list-group-item list-group-item-action" id="contextMenuView">
            <i class="fas fa-eye me-2"></i>View
        </a>
        @if($drive->canEdit(auth()->user()))
        <a href="#" class="list-group-item list-group-item-action" id="contextMenuEdit">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
        <a href="#" class="list-group-item list-group-item-action" id="contextMenuDuplicate">
            <i class="fas fa-copy me-2"></i>Duplicate
        </a>
        <div class="list-group-item">
            <small class="text-muted">Change Status</small>
        </div>
        <div id="contextMenuStatuses"></div>
        <div class="list-group-divider"></div>
        <a href="#" class="list-group-item list-group-item-action text-danger" id="contextMenuArchive">
            <i class="fas fa-archive me-2"></i>Archive
        </a>
        @endif
    </div>
</div>

