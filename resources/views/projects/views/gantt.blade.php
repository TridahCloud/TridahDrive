@push('styles')
<style>
    .gantt-container {
        height: 600px;
        width: 100%;
    }
    .gantt-timeline {
        position: relative;
        overflow-x: auto;
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
                    <button type="button" class="btn btn-outline-primary" onclick="gantt.scaleToFit()">
                        <i class="fas fa-compress-alt me-1"></i>Fit
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="gantt.ext.zoom.setLevel('day')">
                        Day
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="gantt.ext.zoom.setLevel('week')">
                        Week
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="gantt.ext.zoom.setLevel('month')">
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
        $progress = 0;
        if ($task->status === 'done') {
            $progress = 100;
        } elseif ($task->status === 'in_progress') {
            $progress = 50;
        } elseif ($task->status === 'review') {
            $progress = 75;
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
            'status' => $task->status,
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
    
    // Find date range
    const dates = tasks.map(t => new Date(t.start_date));
    const minDate = new Date(Math.min(...dates));
    const maxDate = new Date(Math.max(...tasks.map(t => new Date(t.end_date))));
    
    // Calculate total days
    const totalDays = Math.ceil((maxDate - minDate) / (1000 * 60 * 60 * 24)) + 1;
    const days = [];
    const currentDate = new Date(minDate);
    
    // Generate date labels for the timeline
    while (currentDate <= maxDate) {
        days.push(new Date(currentDate));
        currentDate.setDate(currentDate.getDate() + 1);
    }
    
    // Create timeline HTML
    let timelineHTML = '<div class="gantt-timeline">';
    timelineHTML += '<div class="table-responsive">';
    timelineHTML += '<table class="table table-sm" style="min-width: ' + (totalDays * 30) + 'px;">';
    
    // Header row with dates
    timelineHTML += '<thead><tr><th style="width: 200px;">Task</th>';
    days.forEach((date, index) => {
        if (index % 7 === 0 || index === 0) {
            const weekStart = new Date(date);
            timelineHTML += `<th colspan="7" style="text-align: center; border-left: 2px solid var(--border-color);">${date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}</th>`;
        }
    });
    timelineHTML += '</tr></thead>';
    
    // Task rows
    timelineHTML += '<tbody>';
    tasks.forEach(task => {
        const taskStart = new Date(task.start_date);
        const taskEnd = new Date(task.end_date);
        const taskDays = Math.ceil((taskEnd - taskStart) / (1000 * 60 * 60 * 24)) + 1;
        const startOffset = Math.floor((taskStart - minDate) / (1000 * 60 * 60 * 24));
        
        const statusColors = {
            'todo': '#6c757d',
            'in_progress': '#0d6efd',
            'review': '#0dcaf0',
            'done': '#198754',
            'blocked': '#dc3545'
        };
        
        const priorityColors = {
            'low': '#6c757d',
            'medium': '#0dcaf0',
            'high': '#ffc107',
            'urgent': '#dc3545'
        };
        
        timelineHTML += '<tr>';
        timelineHTML += `<td><a href="${task.url}" class="text-decoration-none"><strong>${task.title}</strong></a><br><small class="text-muted">${task.start_date_formatted} - ${task.end_date_formatted}</small></td>`;
        
        // Generate timeline cells
        let cellCount = 0;
        days.forEach((date, index) => {
            const isTaskDay = date >= taskStart && date <= taskEnd;
            
            if (isTaskDay) {
                if (cellCount === 0) {
                    // First day of task
                    const width = Math.min(taskDays * 30, 300);
                    timelineHTML += `<td colspan="${Math.min(taskDays, totalDays - index)}" style="padding: 0;">`;
                    timelineHTML += `<div class="timeline-bar" style="width: ${width}px; background-color: ${statusColors[task.status] || '#6c757d'}; border-left: 3px solid ${priorityColors[task.priority] || '#6c757d'};">
                        <div class="timeline-bar-progress" style="width: ${task.progress}%;"></div>
                        <div style="position: absolute; top: 0; left: 4px; font-size: 0.75rem; color: white; line-height: 20px;">${task.title}</div>
                    </div>`;
                    timelineHTML += '</td>';
                    cellCount = taskDays;
                }
            } else if (cellCount === 0) {
                timelineHTML += '<td></td>';
            }
            
            if (cellCount > 0) {
                cellCount--;
            }
        });
        
        timelineHTML += '</tr>';
    });
    
    timelineHTML += '</tbody>';
    timelineHTML += '</table>';
    timelineHTML += '</div>';
    timelineHTML += '</div>';
    
    container.innerHTML = timelineHTML;
});
</script>
@endpush
