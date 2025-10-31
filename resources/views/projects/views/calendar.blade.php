@push('styles')
<style>
    .calendar-container {
        background-color: var(--bg-primary);
        border-radius: 8px;
        padding: 1rem;
    }
    .calendar-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .calendar-day-header {
        text-align: center;
        font-weight: bold;
        padding: 0.5rem;
        background-color: var(--bg-secondary);
        border-radius: 4px;
        font-size: 0.875rem;
    }
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.5rem;
    }
    .calendar-day {
        min-height: 100px;
        background-color: var(--bg-secondary);
        border-radius: 4px;
        padding: 0.5rem;
        border: 1px solid var(--border-color);
        position: relative;
    }
    .calendar-day.other-month {
        opacity: 0.5;
        background-color: var(--bg-tertiary);
    }
    .calendar-day-number {
        font-weight: bold;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }
    .calendar-day.today {
        border: 2px solid var(--brand-teal);
        background-color: rgba(49, 216, 178, 0.1);
    }
    .calendar-task {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        margin-bottom: 0.25rem;
        border-radius: 3px;
        cursor: pointer;
        transition: all 0.2s;
        border-left: 3px solid;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .calendar-task:hover {
        transform: translateX(2px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .calendar-task.priority-low { border-left-color: #6c757d; }
    .calendar-task.priority-medium { border-left-color: #0dcaf0; }
    .calendar-task.priority-high { border-left-color: #ffc107; }
    .calendar-task.priority-urgent { border-left-color: #dc3545; }
    .calendar-task.status-todo { background-color: rgba(108, 117, 125, 0.2); }
    .calendar-task.status-in_progress { background-color: rgba(13, 110, 253, 0.2); }
    .calendar-task.status-review { background-color: rgba(13, 202, 240, 0.2); }
    .calendar-task.status-done { background-color: rgba(25, 135, 84, 0.2); }
    .calendar-task.status-blocked { background-color: rgba(220, 53, 69, 0.2); }
    .calendar-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    .calendar-nav-title {
        font-size: 1.25rem;
        font-weight: bold;
    }
    .calendar-view-switcher {
        display: flex;
        gap: 0.5rem;
    }
</style>
@endpush

<div class="row">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Calendar View</h5>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary" onclick="changeMonth(-1)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="goToToday()">
                        Today
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="changeMonth(1)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div id="calendar-container" class="calendar-container">
                <div id="calendar-nav" class="calendar-nav">
                    <button class="btn btn-sm btn-outline-primary" onclick="changeMonth(-1)">
                        <i class="fas fa-chevron-left"></i> Prev
                    </button>
                    <div class="calendar-nav-title" id="calendar-title">Loading...</div>
                    <button class="btn btn-sm btn-outline-primary" onclick="changeMonth(1)">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div id="calendar-content"></div>
            </div>

            @if($project->tasks->whereNull('deleted_at')->isEmpty())
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    No tasks available to display in calendar. <a href="{{ route('drives.projects.projects.tasks.create', [$drive, $project]) }}">Create your first task</a> to get started.
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
@php
    $calendarTasks = $project->tasks->whereNull('deleted_at')->map(function($task) use ($drive, $project) {
        $startDate = $task->due_date ?? $task->start_date ?? $task->created_at;
        $endDate = $task->due_date ?? $task->start_date ?? $task->created_at;
        
        return [
            'id' => $task->id,
            'title' => $task->title,
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate ? $endDate->format('Y-m-d') : $startDate->format('Y-m-d'),
            'status' => $task->status,
            'priority' => $task->priority,
            'url' => route('drives.projects.projects.tasks.show', [$drive, $project, $task]),
        ];
    });
@endphp

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('calendar-content');
    const titleEl = document.getElementById('calendar-title');
    const tasks = @json($calendarTasks);
    
    let currentDate = new Date();
    
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                        'July', 'August', 'September', 'October', 'November', 'December'];
    
    function renderCalendar(date) {
        const year = date.getFullYear();
        const month = date.getMonth();
        
        // Set title
        titleEl.textContent = `${monthNames[month]} ${year}`;
        
        // Get first day of month and number of days
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDayOfWeek = firstDay.getDay();
        
        // Get tasks for this month
        const monthTasks = tasks.filter(task => {
            const taskDate = new Date(task.start);
            return taskDate.getMonth() === month && taskDate.getFullYear() === year;
        });
        
        // Create calendar HTML
        let html = '<div class="calendar-header">';
        dayNames.forEach(day => {
            html += `<div class="calendar-day-header">${day}</div>`;
        });
        html += '</div>';
        
        html += '<div class="calendar-grid">';
        
        // Add empty cells for days before month starts
        for (let i = 0; i < startingDayOfWeek; i++) {
            const prevDate = new Date(year, month, -i);
            html += `<div class="calendar-day other-month">
                <div class="calendar-day-number">${prevDate.getDate()}</div>
            </div>`;
        }
        
        // Add days of the month
        const today = new Date();
        for (let day = 1; day <= daysInMonth; day++) {
            const cellDate = new Date(year, month, day);
            const isToday = cellDate.toDateString() === today.toDateString();
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            
            // Get tasks for this day
            const dayTasks = monthTasks.filter(task => task.start === dateStr);
            
            html += `<div class="calendar-day ${isToday ? 'today' : ''}">
                <div class="calendar-day-number">${day}</div>`;
            
            dayTasks.forEach(task => {
                html += `<div class="calendar-task priority-${task.priority} status-${task.status}" 
                            onclick="window.location.href='${task.url}'" 
                            title="${task.title}">
                    ${task.title}
                </div>`;
            });
            
            html += '</div>';
        }
        
        // Add empty cells for days after month ends
        const remainingCells = (7 - ((startingDayOfWeek + daysInMonth) % 7)) % 7;
        for (let i = 1; i <= remainingCells; i++) {
            html += `<div class="calendar-day other-month">
                <div class="calendar-day-number">${i}</div>
            </div>`;
        }
        
        html += '</div>';
        
        container.innerHTML = html;
    }
    
    window.changeMonth = function(delta) {
        currentDate.setMonth(currentDate.getMonth() + delta);
        renderCalendar(new Date(currentDate));
    };
    
    window.goToToday = function() {
        currentDate = new Date();
        renderCalendar(new Date(currentDate));
    };
    
    // Initial render
    renderCalendar(new Date(currentDate));
});
</script>
@endpush
