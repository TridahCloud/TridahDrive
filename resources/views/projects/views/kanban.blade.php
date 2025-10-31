<div class="row">
    <div class="col-12">
        <div class="kanban-board">
            <div class="row g-3 h-100">
                <!-- Todo Column -->
                <div class="col-lg-2 col-md-4 col-sm-6 h-100">
                    <div class="dashboard-card h-100 d-flex flex-column" style="padding: 0;">
                        <div class="kanban-column-header p-3">
                            <h6 class="mb-0">
                                <span class="badge bg-secondary me-2">{{ $tasksByStatus['todo']->count() }}</span>
                                Todo
                            </h6>
                        </div>
                        <div id="kanban-todo" data-status="todo" class="kanban-column-content">
                            <div class="kanban-empty-state text-center text-muted py-5" id="empty-todo" style="display: {{ $tasksByStatus['todo']->isEmpty() ? 'block' : 'none' }};">
                                <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
                                <p class="small mb-0">No tasks</p>
                            </div>
                            @foreach($tasksByStatus['todo'] as $task)
                                @include('projects.partials.task-card', ['task' => $task])
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- In Progress Column -->
                <div class="col-lg-2 col-md-4 col-sm-6 h-100">
                    <div class="dashboard-card h-100 d-flex flex-column" style="padding: 0;">
                        <div class="kanban-column-header p-3">
                            <h6 class="mb-0">
                                <span class="badge bg-primary me-2">{{ $tasksByStatus['in_progress']->count() }}</span>
                                In Progress
                            </h6>
                        </div>
                        <div id="kanban-in_progress" data-status="in_progress" class="kanban-column-content">
                            <div class="kanban-empty-state text-center text-muted py-5" id="empty-in_progress" style="display: {{ $tasksByStatus['in_progress']->isEmpty() ? 'block' : 'none' }};">
                                <i class="fas fa-spinner fa-2x mb-2 opacity-50"></i>
                                <p class="small mb-0">No tasks</p>
                            </div>
                            @foreach($tasksByStatus['in_progress'] as $task)
                                @include('projects.partials.task-card', ['task' => $task])
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Review Column -->
                <div class="col-lg-2 col-md-4 col-sm-6 h-100">
                    <div class="dashboard-card h-100 d-flex flex-column" style="padding: 0;">
                        <div class="kanban-column-header p-3">
                            <h6 class="mb-0">
                                <span class="badge bg-info me-2">{{ $tasksByStatus['review']->count() }}</span>
                                Review
                            </h6>
                        </div>
                        <div id="kanban-review" data-status="review" class="kanban-column-content">
                            <div class="kanban-empty-state text-center text-muted py-5" id="empty-review" style="display: {{ $tasksByStatus['review']->isEmpty() ? 'block' : 'none' }};">
                                <i class="fas fa-search fa-2x mb-2 opacity-50"></i>
                                <p class="small mb-0">No tasks</p>
                            </div>
                            @foreach($tasksByStatus['review'] as $task)
                                @include('projects.partials.task-card', ['task' => $task])
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Done Column -->
                <div class="col-lg-2 col-md-4 col-sm-6 h-100">
                    <div class="dashboard-card h-100 d-flex flex-column" style="padding: 0;">
                        <div class="kanban-column-header p-3">
                            <h6 class="mb-0">
                                <span class="badge bg-success me-2">{{ $tasksByStatus['done']->count() }}</span>
                                Done
                            </h6>
                        </div>
                        <div id="kanban-done" data-status="done" class="kanban-column-content">
                            <div class="kanban-empty-state text-center text-muted py-5" id="empty-done" style="display: {{ $tasksByStatus['done']->isEmpty() ? 'block' : 'none' }};">
                                <i class="fas fa-check-circle fa-2x mb-2 opacity-50"></i>
                                <p class="small mb-0">No tasks</p>
                            </div>
                            @foreach($tasksByStatus['done'] as $task)
                                @include('projects.partials.task-card', ['task' => $task])
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Blocked Column -->
                <div class="col-lg-2 col-md-4 col-sm-6 h-100">
                    <div class="dashboard-card h-100 d-flex flex-column" style="padding: 0;">
                        <div class="kanban-column-header p-3">
                            <h6 class="mb-0">
                                <span class="badge bg-danger me-2">{{ $tasksByStatus['blocked']->count() }}</span>
                                Blocked
                            </h6>
                        </div>
                        <div id="kanban-blocked" data-status="blocked" class="kanban-column-content">
                            <div class="kanban-empty-state text-center text-muted py-5" id="empty-blocked" style="display: {{ $tasksByStatus['blocked']->isEmpty() ? 'block' : 'none' }};">
                                <i class="fas fa-ban fa-2x mb-2 opacity-50"></i>
                                <p class="small mb-0">No tasks</p>
                            </div>
                            @foreach($tasksByStatus['blocked'] as $task)
                                @include('projects.partials.task-card', ['task' => $task])
                            @endforeach
                        </div>
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

