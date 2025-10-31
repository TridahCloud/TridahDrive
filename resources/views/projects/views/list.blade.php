<div class="row">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Tasks</h5>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" style="width: auto;" id="filterStatus">
                        <option value="">All Status</option>
                        <option value="todo">Todo</option>
                        <option value="in_progress">In Progress</option>
                        <option value="review">Review</option>
                        <option value="done">Done</option>
                        <option value="blocked">Blocked</option>
                    </select>
                    <select class="form-select form-select-sm" style="width: auto;" id="filterPriority">
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Owner</th>
                            <th>Members</th>
                            <th>Labels</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tasksTableBody">
                        @forelse($tasksByStatus as $status => $tasks)
                            @foreach($tasks as $task)
                                <tr data-status="{{ $task->status }}" data-priority="{{ $task->priority }}">
                                    <td>
                                        <a href="{{ route('drives.projects.projects.tasks.show', [$drive, $project, $task]) }}" class="text-decoration-none">
                                            <strong>{{ $task->title }}</strong>
                                        </a>
                                        @if($task->description)
                                            <br><small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $task->status === 'done' ? 'success' : ($task->status === 'todo' ? 'secondary' : ($task->status === 'blocked' ? 'danger' : 'primary')) }}">
                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : ($task->priority === 'medium' ? 'info' : 'secondary')) }}">
                                            {{ ucfirst($task->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($task->owner)
                                            <span class="badge bg-info">{{ $task->owner->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->members->count() > 0)
                                            @foreach($task->members->take(3) as $member)
                                                <span class="badge bg-secondary">{{ Str::limit($member->name, 10) }}</span>
                                            @endforeach
                                            @if($task->members->count() > 3)
                                                <span class="badge bg-secondary">+{{ $task->members->count() - 3 }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->labels->count() > 0)
                                            @foreach($task->labels as $label)
                                                <span class="badge" style="background-color: {{ $label->color }};">{{ $label->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->due_date)
                                            @if($task->isOverdue())
                                                <span class="text-danger">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    {{ $task->due_date->format('M d, Y') }}
                                                </span>
                                            @else
                                                {{ $task->due_date->format('M d, Y') }}
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('drives.projects.projects.tasks.edit', [$drive, $project, $task]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('drives.projects.projects.tasks.destroy', [$drive, $project, $task]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-tasks fa-2x mb-2"></i>
                                    <p>No tasks yet. <a href="{{ route('drives.projects.projects.tasks.create', [$drive, $project]) }}">Create your first task</a></p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterStatus = document.getElementById('filterStatus');
    const filterPriority = document.getElementById('filterPriority');
    const tableBody = document.getElementById('tasksTableBody');
    
    function filterTasks() {
        const statusFilter = filterStatus.value;
        const priorityFilter = filterPriority.value;
        const rows = tableBody.querySelectorAll('tr');
        
        rows.forEach(row => {
            const status = row.dataset.status;
            const priority = row.dataset.priority;
            
            let show = true;
            if (statusFilter && status !== statusFilter) show = false;
            if (priorityFilter && priority !== priorityFilter) show = false;
            
            row.style.display = show ? '' : 'none';
        });
    }
    
    if (filterStatus) filterStatus.addEventListener('change', filterTasks);
    if (filterPriority) filterPriority.addEventListener('change', filterTasks);
});
</script>
@endpush

