<div class="row">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Tasks</h5>
                <div class="d-flex flex-wrap gap-2">
                    <select class="form-select form-select-sm" style="width: auto;" id="filterStatus">
                        <option value="">All Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->slug }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                    <select class="form-select form-select-sm" style="width: auto;" id="filterPriority">
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                    <select class="form-select form-select-sm" style="width: auto;" id="filterLabel">
                        <option value="">All Labels</option>
                        @foreach($labels as $label)
                            <option value="{{ $label->id }}">{{ $label->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="projectTasksTable">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort-key="title">Title <span class="sort-indicator"></span></th>
                            <th class="sortable" data-sort-key="status">Status <span class="sort-indicator"></span></th>
                            <th class="sortable" data-sort-key="priority">Priority <span class="sort-indicator"></span></th>
                            <th class="sortable" data-sort-key="owner">Owner <span class="sort-indicator"></span></th>
                            <th class="sortable" data-sort-key="members">Members <span class="sort-indicator"></span></th>
                            <th class="sortable" data-sort-key="labels">Labels <span class="sort-indicator"></span></th>
                            <th class="sortable" data-sort-key="due">Due Date <span class="sort-indicator"></span></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tasksTableBody">
                        @forelse($tasksByStatus as $statusSlug => $tasks)
                            @foreach($tasks as $task)
                                @php
                                    $statusName = $task->status->name ?? 'Unassigned';
                                    $priorityWeights = ['low' => 1, 'medium' => 2, 'high' => 3, 'urgent' => 4];
                                    $priorityWeight = $priorityWeights[$task->priority] ?? 0;
                                    $ownerName = $task->owner->name ?? '';
                                    $membersNames = $task->members->pluck('name')->join(', ');
                                    $labelsNames = $task->labels->pluck('name')->join(', ');
                                    $dueTimestamp = $task->due_date ? $task->due_date->timestamp : '';
                                @endphp
                                <tr data-status="{{ $task->status_slug }}" data-priority="{{ $task->priority }}"
                                    data-title="{{ strtolower($task->title) }}"
                                    data-status-sort="{{ strtolower($statusName) }}"
                                    data-priority-weight="{{ $priorityWeight }}"
                                    data-owner="{{ strtolower($ownerName) }}"
                                    data-members="{{ strtolower($membersNames) }}"
                                    data-labels="{{ strtolower($labelsNames) }}"
                                    data-due="{{ $dueTimestamp }}"
                                    @if($task->priority === 'urgent') class="table-warning" @endif>
                                    <td>
                                        <a href="{{ route('drives.projects.projects.tasks.show', [$drive, $project, $task]) }}" class="text-decoration-none">
                                            <strong>{{ $task->title }}</strong>
                                        </a>
                                        @if($task->description)
                                            <br><small class="text-muted">{{ Str::limit(strip_tags($task->description), 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->status)
                                            <span class="badge task-status-badge" style="background-color: {{ $task->status->color }}; color: #fff;">
                                                {{ $task->status->name }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Unassigned</span>
                                        @endif
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

@push('styles')
<style>
    #projectTasksTable th.sortable {
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
    }

    #projectTasksTable th.sortable .sort-indicator {
        display: inline-flex;
        align-items: center;
        margin-left: 0.35rem;
        font-size: 0.75rem;
        opacity: 0.45;
    }

    #projectTasksTable th.sortable.sorted-asc .sort-indicator::after {
        content: '\25B2';
    }

    #projectTasksTable th.sortable.sorted-desc .sort-indicator::after {
        content: '\25BC';
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterStatus = document.getElementById('filterStatus');
    const filterPriority = document.getElementById('filterPriority');
    const filterLabel = document.getElementById('filterLabel');
    const tableBody = document.getElementById('tasksTableBody');
    const sortableHeaders = document.querySelectorAll('#projectTasksTable th.sortable');

    function filterTasks() {
        const statusFilter = filterStatus.value;
        const priorityFilter = filterPriority.value;
        const labelFilter = filterLabel.value;
        const rows = tableBody.querySelectorAll('tr');
        
        rows.forEach(row => {
            const status = row.dataset.status;
            const priority = row.dataset.priority;
            const labels = row.dataset.labels ? row.dataset.labels.split(',').filter(Boolean) : [];
            
            let show = true;
            if (statusFilter && status !== statusFilter) show = false;
            if (priorityFilter && priority !== priorityFilter) show = false;
            if (labelFilter && !labels.includes(labelFilter)) show = false;
            
            row.style.display = show ? '' : 'none';
        });
    }

    if (filterStatus) filterStatus.addEventListener('change', filterTasks);
    if (filterPriority) filterPriority.addEventListener('change', filterTasks);
    if (filterLabel) filterLabel.addEventListener('change', filterTasks);

    let currentSortKey = null;
    let currentSortDirection = 'asc';

    function getSortValue(row, key, direction) {
        switch (key) {
            case 'priority':
                return Number(row.dataset.priorityWeight || 0);
            case 'due': {
                const value = row.dataset.due;
                if (!value) {
                    return direction === 'asc' ? Number.MAX_SAFE_INTEGER : Number.MIN_SAFE_INTEGER;
                }
                const parsed = parseInt(value, 10);
                return Number.isNaN(parsed) ? (direction === 'asc' ? Number.MAX_SAFE_INTEGER : Number.MIN_SAFE_INTEGER) : parsed;
            }
            case 'status':
                return (row.dataset.statusSort || '').toString();
            case 'title':
            case 'owner':
            case 'members':
            case 'labels':
            default:
                return (row.dataset[key] || '').toString();
        }
    }

    function updateHeaderIndicators(key, direction) {
        sortableHeaders.forEach(header => {
            header.classList.remove('sorted-asc', 'sorted-desc');
            header.setAttribute('aria-sort', 'none');
        });
        if (!key) {
            return;
        }
        const activeHeader = document.querySelector(`#projectTasksTable th[data-sort-key="${key}"]`);
        if (activeHeader) {
            const sortClass = direction === 'asc' ? 'sorted-asc' : 'sorted-desc';
            activeHeader.classList.add(sortClass);
            activeHeader.setAttribute('aria-sort', direction === 'asc' ? 'ascending' : 'descending');
        }
    }

    function sortTasks(key) {
        const rows = Array.from(tableBody.querySelectorAll('tr'));
        const direction = (currentSortKey === key && currentSortDirection === 'asc') ? 'desc' : 'asc';

        rows.sort((rowA, rowB) => {
            const aVal = getSortValue(rowA, key, direction);
            const bVal = getSortValue(rowB, key, direction);

            if (typeof aVal === 'number' && typeof bVal === 'number') {
                return direction === 'asc' ? aVal - bVal : bVal - aVal;
            }

            return direction === 'asc'
                ? aVal.localeCompare(bVal)
                : bVal.localeCompare(aVal);
        });

        rows.forEach(row => tableBody.appendChild(row));

        currentSortKey = key;
        currentSortDirection = direction;
        updateHeaderIndicators(key, direction);
    }

    sortableHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const sortKey = header.dataset.sortKey;
            if (!sortKey) {
                return;
            }
            sortTasks(sortKey);
        });
    });
});
</script>
@endpush

