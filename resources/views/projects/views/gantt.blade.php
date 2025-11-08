@push('styles')
<style>
    .gantt-container {
        width: 100%;
        height: auto;
        min-height: 600px;
    }
    .gantt-timeline {
        position: relative;
        overflow-x: auto;
        overflow-y: visible;
        background-color: var(--bg-primary);
        border-radius: 0.75rem;
        padding: 1rem;
        box-shadow: inset 0 0 0 1px var(--border-color);
    }
    .gantt-timeline .table-responsive {
        margin-bottom: 0;
        background-color: transparent;
    }
    .gantt-timeline .table {
        background-color: transparent;
        margin-bottom: 0;
        color: var(--text-color);
    }
    .gantt-timeline th,
    .gantt-timeline td {
        background-color: transparent;
        border-color: var(--border-color);
        white-space: nowrap;
    }
    .gantt-timeline .timeline-cell {
        padding: 0.25rem;
        vertical-align: middle;
    }
    .timeline-task {
        margin-bottom: 1rem;
        padding: 0.5rem;
        background-color: var(--bg-secondary);
        border-radius: 4px;
        border-left: 4px solid var(--brand-teal);
    }
    .timeline-task-header {
        font-weight: bold;
        margin-bottom: 0.25rem;
    }
    .timeline-task-dates {
        font-size: 0.875rem;
        color: var(--text-muted);
    }
    .timeline-bar {
        height: 20px;
        background-color: var(--brand-teal);
        border-radius: 2px;
        margin-top: 0.5rem;
        position: relative;
    }
    .timeline-bar-progress {
        height: 100%;
        background-color: rgba(255, 255, 255, 0.3);
        border-radius: 2px;
    }
</style>
@endpush

<div class="row">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Gantt Chart</h5>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary" data-scale="day">
                        Day
                    </button>
                    <button type="button" class="btn btn-outline-primary" data-scale="week">
                        Week
                    </button>
                    <button type="button" class="btn btn-outline-primary" data-scale="month">
                        Month
                    </button>
                </div>
            </div>

            <div id="gantt-container" class="gantt-container"></div>

            @if($project->tasks->whereNull('deleted_at')->isEmpty())
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    No tasks available to display in Gantt chart. <a href="{{ route('drives.projects.projects.tasks.create', [$drive, $project]) }}">Create your first task</a> to get started.
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
@php
    $ganttTasks = $project->tasks->whereNull('deleted_at')->map(function($task) use ($drive, $project) {
        $startDate = $task->start_date ? $task->start_date : $task->created_at;
        $endDate = $task->due_date ? $task->due_date : ($task->start_date ? $task->start_date->copy()->addDays(1) : $task->created_at->copy()->addDays(1));
        
        // Calculate duration in days
        $duration = 1;
        if ($startDate && $endDate) {
            $duration = max(1, $startDate->diffInDays($endDate));
        }
        
        // Calculate progress percentage
        $statusModel = $task->status;
        $statusSlug = $statusModel?->slug ?? 'other';
        $statusColor = $statusModel?->color ?? '#6B7280';

        $progress = 0;
        if ($statusModel?->is_completed) {
            $progress = 100;
        } elseif ($statusSlug === 'review') {
            $progress = 75;
        } elseif ($statusSlug === 'in_progress') {
            $progress = 50;
        }
        
        return [
            'id' => $task->id,
            'title' => $task->title,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'start_date_formatted' => $startDate->format('M d, Y'),
            'end_date_formatted' => $endDate->format('M d, Y'),
            'duration' => $duration,
            'progress' => $progress,
            'status' => [
                'slug' => $statusSlug,
                'name' => $statusModel?->name,
                'color' => $statusColor,
                'is_completed' => (bool) $statusModel?->is_completed,
            ],
            'priority' => $task->priority,
            'url' => route('drives.projects.projects.tasks.show', [$drive, $project, $task]),
        ];
    });
    
    // Sort by start date
    $ganttTasks = $ganttTasks->sortBy('start_date')->values();
@endphp

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('gantt-container');
    const scaleButtons = document.querySelectorAll('[data-scale]');
    const tasks = @json($ganttTasks);

    if (tasks.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No tasks available to display in Gantt chart. 
                <a href="{{ route('drives.projects.projects.tasks.create', [$drive, $project]) }}">Create your first task</a> to get started.
            </div>
        `;
        return;
    }

    let currentScale = 'day';

    function renderGantt(scale) {
        const dayMs = 24 * 60 * 60 * 1000;
        const scaleKey = scale;

        function buildUnits(unitScale, minDate, maxDate) {
            const units = [];
            if (unitScale === 'day') {
                let cursor = new Date(minDate);
                while (cursor <= maxDate) {
                    const start = new Date(cursor);
                    const end = new Date(cursor);
                    units.push({
                        start,
                        end,
                        spanDays: 1,
                        label: start.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
                    });
                    cursor.setDate(cursor.getDate() + 1);
                }
            } else if (unitScale === 'week') {
                let cursor = new Date(minDate);
                while (cursor <= maxDate) {
                    const start = new Date(cursor);
                    const end = new Date(cursor);
                    end.setDate(end.getDate() + 6);
                    if (end > maxDate) {
                        end.setTime(maxDate.getTime());
                    }
                    const spanDays = Math.floor((end - start) / dayMs) + 1;
                    units.push({
                        start,
                        end,
                        spanDays,
                        label: `Week of ${start.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`
                    });
                    cursor = new Date(end);
                    cursor.setDate(cursor.getDate() + 1);
                }
            } else if (unitScale === 'month') {
                let cursor = new Date(minDate.getFullYear(), minDate.getMonth(), 1);
                while (cursor <= maxDate) {
                    const start = cursor < minDate ? new Date(minDate) : new Date(cursor);
                    const monthEnd = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 0);
                    const end = monthEnd > maxDate ? new Date(maxDate) : monthEnd;
                    const spanDays = Math.floor((end - start) / dayMs) + 1;
                    units.push({
                        start,
                        end,
                        spanDays,
                        label: start.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
                    });
                    cursor = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 1);
                }
            }
            return units;
        }

        const dateValues = tasks.flatMap(task => [task.start_date, task.end_date]).map(d => new Date(d));
        const minDate = new Date(Math.min(...dateValues.map(d => d.getTime())));
        const maxDate = new Date(Math.max(...dateValues.map(d => d.getTime())));

        const units = buildUnits(scaleKey, minDate, maxDate);
        if (!units.length) {
            container.innerHTML = '';
            return;
        }

        const totalSpanDays = units.reduce((sum, unit) => sum + unit.spanDays, 0);
        const containerWidth = container.clientWidth || container.offsetWidth || 960;

        let basePerDay;
        switch (scale) {
            case 'day':
                basePerDay = 120;
                break;
            case 'week':
                basePerDay = 28;
                break;
            case 'month':
            default:
                basePerDay = 12;
                break;
        }

        const unitWidths = units.map(unit => Math.max(unit.spanDays * basePerDay, 40));
        const tableMinWidth = unitWidths.reduce((acc, width) => acc + width, 0);

        let html = '<div class="gantt-timeline">';
        html += '<div class="table-responsive">';
        html += `<table class="table table-sm" style="min-width: ${tableMinWidth}px;">`;

        html += '<thead><tr><th style="width: 220px;">Task</th>';
        units.forEach((unit, idx) => {
            html += `<th style="min-width: ${unitWidths[idx]}px;">${unit.label}</th>`;
        });
        html += '</tr></thead>';

        const priorityColors = {
            'low': '#6c757d',
            'medium': '#0dcaf0',
            'high': '#ffc107',
            'urgent': '#dc3545'
        };

        html += '<tbody>';
        tasks.forEach(task => {
            const taskStart = new Date(task.start_date);
            const taskEnd = new Date(task.end_date);

            let startIndex = 0;
            while (startIndex < units.length && taskStart > units[startIndex].end) {
                startIndex++;
            }
            let endIndex = units.length - 1;
            while (endIndex >= 0 && taskEnd < units[endIndex].start) {
                endIndex--;
            }

            if (startIndex >= units.length || endIndex < 0 || startIndex > endIndex) {
                return;
            }

            html += '<tr>';
            html += `<td><a href="${task.url}" class="text-decoration-none"><strong>${task.title}</strong></a><br><small class="text-muted">${task.start_date_formatted} - ${task.end_date_formatted}</small></td>`;

            for (let i = 0; i < startIndex; i++) {
                html += `<td style="min-width: ${unitWidths[i]}px;"></td>`;
            }

            const span = endIndex - startIndex + 1;
            const barWidth = unitWidths.slice(startIndex, endIndex + 1).reduce((acc, width) => acc + width, 0);
            const statusColor = task.status && task.status.color ? task.status.color : '#6c757d';
            const priorityColor = priorityColors[task.priority] || '#6c757d';

            html += `<td colspan="${span}" style="min-width: ${barWidth}px; width: ${barWidth}px;" class="timeline-cell">`;
            html += `<div class="timeline-bar" style="background-color: ${statusColor}; border-left: 3px solid ${priorityColor}; width: 100%; min-width: ${barWidth}px;">
                <div class="timeline-bar-progress" style="width: ${task.progress}%;"></div>
                <div style="position: absolute; top: 0; left: 4px; font-size: 0.75rem; color: white; line-height: 20px;">${task.title}</div>
            </div>`;
            html += '</td>';

            for (let i = endIndex + 1; i < units.length; i++) {
                html += `<td style="min-width: ${unitWidths[i]}px;"></td>`;
            }

            html += '</tr>';
        });

        html += '</tbody>';
        html += '</table>';
        html += '</div>';
        html += '</div>';

        container.innerHTML = html;
    }

    function updateActiveButton(scale) {
        scaleButtons.forEach(btn => {
            if (btn.dataset.scale === scale) {
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary');
            } else {
                btn.classList.add('btn-outline-primary');
                btn.classList.remove('btn-primary');
            }
        });
    }

    scaleButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const scale = btn.dataset.scale;
            currentScale = scale;
            updateActiveButton(scale);
            renderGantt(scale);
        });
    });

    updateActiveButton(currentScale);
    renderGantt(currentScale);
});
</script>
@endpush
