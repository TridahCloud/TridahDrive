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
    .calendar-weeks {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .calendar-week {
        border-radius: 4px;
        padding: 0.25rem 0.25rem 0.5rem;
        background-color: transparent;
    }
    .calendar-week-grid {
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
    .calendar-week-bars {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        grid-auto-rows: minmax(26px, auto);
        gap: 0.5rem;
        margin-top: 0.35rem;
    }
    .calendar-week-bars--empty {
        min-height: 0;
        margin-top: 0;
    }
    .calendar-task,
    .calendar-task-bar {
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
    .calendar-task-bar {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        min-height: 24px;
    }
    .calendar-task:hover,
    .calendar-task-bar:hover {
        transform: translateX(2px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .calendar-task.priority-low,
    .calendar-task-bar.priority-low { border-left-color: #6c757d; }
    .calendar-task.priority-medium,
    .calendar-task-bar.priority-medium { border-left-color: #0dcaf0; }
    .calendar-task.priority-high,
    .calendar-task-bar.priority-high { border-left-color: #ffc107; }
    .calendar-task.priority-urgent,
    .calendar-task-bar.priority-urgent { border-left-color: #dc3545; }
    .calendar-task.status-todo,
    .calendar-task-bar.status-todo { background-color: rgba(108, 117, 125, 0.2); }
    .calendar-task.status-in_progress,
    .calendar-task-bar.status-in_progress { background-color: rgba(13, 110, 253, 0.2); }
    .calendar-task.status-review,
    .calendar-task-bar.status-review { background-color: rgba(13, 202, 240, 0.2); }
    .calendar-task.status-done,
    .calendar-task-bar.status-done { background-color: rgba(25, 135, 84, 0.2); }
    .calendar-task.status-blocked,
    .calendar-task-bar.status-blocked { background-color: rgba(220, 53, 69, 0.2); }
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
        $startDate = $task->start_date ?? $task->created_at->copy();
        $endDate = $task->due_date ?? ($task->start_date ? $task->start_date->copy() : $task->created_at->copy());

        if ($endDate->lt($startDate)) {
            $endDate = $startDate->copy();
        }
        
        return [
            'id' => $task->id,
            'title' => $task->title,
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d'),
            'status' => [
                'slug' => $task->status?->slug,
                'name' => $task->status?->name,
                'color' => $task->status?->color ?? '#6B7280',
            ],
            'priority' => $task->priority,
            'url' => route('drives.projects.projects.tasks.show', [$drive, $project, $task]),
        ];
    });
@endphp

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('calendar-content');
    const titleEl = document.getElementById('calendar-title');
    const rawTasks = @json($calendarTasks);

    const parseDate = (dateStr) => {
        const [year, month, day] = dateStr.split('-').map(Number);
        return new Date(year, month - 1, day);
    };

    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    const dayInMs = 24 * 60 * 60 * 1000;
    const diffInDays = (later, earlier) => Math.round((later.getTime() - earlier.getTime()) / dayInMs);

    const tasks = rawTasks.map(task => ({
        ...task,
        startDate: parseDate(task.start),
        endDate: parseDate(task.end),
    }));

    const tasksByStart = rawTasks.reduce((acc, task) => {
        (acc[task.start] = acc[task.start] || []).push(task);
        return acc;
    }, {});

    let currentDate = new Date();

    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];

    function renderCalendar(date) {
        const year = date.getFullYear();
        const month = date.getMonth();

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        titleEl.textContent = `${monthNames[month]} ${year}`;

        const monthStart = new Date(year, month, 1);
        const monthEnd = new Date(year, month + 1, 0);

        const calendarStart = new Date(monthStart);
        calendarStart.setDate(calendarStart.getDate() - calendarStart.getDay());

        const calendarEnd = new Date(monthEnd);
        calendarEnd.setDate(calendarEnd.getDate() + (6 - calendarEnd.getDay()));

        const weeks = [];
        let iterator = new Date(calendarStart);

        while (iterator <= calendarEnd) {
            const weekDays = [];

            for (let i = 0; i < 7; i++) {
                const dayDate = new Date(iterator);
                dayDate.setHours(0, 0, 0, 0);
                const dateStr = formatDate(dayDate);
                const startTasks = tasksByStart[dateStr] || [];
                const singleDayTasks = startTasks.filter(task => task.start === task.end);

                weekDays.push({
                    date: new Date(dayDate),
                    dateStr,
                    number: dayDate.getDate(),
                    isCurrentMonth: dayDate.getMonth() === month,
                    isToday: dayDate.getTime() === today.getTime(),
                    singleDayTasks,
                });

                iterator.setDate(iterator.getDate() + 1);
            }

            weeks.push({ days: weekDays });
        }

        weeks.forEach(week => {
            const weekStart = week.days[0].date;
            const weekEnd = week.days[6].date;

            const overlapping = tasks
                .filter(task => task.startDate <= weekEnd && task.endDate >= weekStart)
                .map(task => {
                    const isSingleDay = task.startDate.toDateString() === task.endDate.toDateString();
                    if (isSingleDay) {
                        return null;
                    }
                    const segmentStart = task.startDate > weekStart ? task.startDate : weekStart;
                    const segmentEnd = task.endDate < weekEnd ? task.endDate : weekEnd;
                    const startIndex = Math.max(0, Math.min(6, diffInDays(segmentStart, weekStart)));
                    const endIndex = Math.max(startIndex, Math.min(6, diffInDays(segmentEnd, weekStart)));

                    return { task, startIndex, endIndex };
                })
                .filter(Boolean)
                .sort((a, b) => {
                    if (a.task.startDate.getTime() === b.task.startDate.getTime()) {
                        return b.task.endDate - a.task.endDate;
                    }
                    return a.task.startDate - b.task.startDate;
                });

            const levels = [];
            week.segments = overlapping.map(segment => {
                let level = 0;
                while (levels[level] !== undefined && levels[level] >= segment.startIndex) {
                    level++;
                }
                levels[level] = segment.endIndex;
                return { ...segment, level };
            });
            week.levelCount = levels.length;
        });

        let html = '<div class="calendar-header">';
        dayNames.forEach(day => {
            html += `<div class="calendar-day-header">${day}</div>`;
        });
        html += '</div>';

        html += '<div class="calendar-weeks">';
        weeks.forEach(week => {
            html += '<div class="calendar-week">';
            html += '<div class="calendar-week-grid">';

            week.days.forEach(day => {
                html += `<div class="calendar-day ${day.isCurrentMonth ? '' : 'other-month'} ${day.isToday ? 'today' : ''}">`;
                html += `<div class="calendar-day-number">${day.number}</div>`;

                day.singleDayTasks.forEach(task => {
                    const statusColor = task.status && task.status.color ? task.status.color : '#6B7280';
                    const statusBackground = statusColor.length === 7 ? `${statusColor}33` : statusColor;
                    html += `<div class="calendar-task priority-${task.priority}" 
                                style="background-color: ${statusBackground}; border-left-color: ${statusColor};"
                                onclick="window.location.href='${task.url}'"
                                title="${task.title}">
                                ${task.title}
                            </div>`;
                });

                html += '</div>';
            });

            html += '</div>';

            if (week.segments.length > 0) {
                html += `<div class="calendar-week-bars" style="grid-template-rows: repeat(${week.levelCount}, minmax(26px, auto));">`;
                week.segments.forEach(segment => {
                    const task = segment.task;
                    const statusColor = task.status && task.status.color ? task.status.color : '#6B7280';
                    const statusBackground = statusColor.length === 7 ? `${statusColor}55` : statusColor;

                    html += `<div class="calendar-task-bar priority-${task.priority}" 
                                style="grid-column: ${segment.startIndex + 1} / ${segment.endIndex + 2}; grid-row: ${segment.level + 1}; background-color: ${statusBackground}; border-left-color: ${statusColor};"
                                onclick="window.location.href='${task.url}'"
                                title="${task.title}">
                                <span>${task.title}</span>
                            </div>`;
                });
                html += '</div>';
            } else {
                html += '<div class="calendar-week-bars calendar-week-bars--empty"></div>';
            }

            html += '</div>';
        });
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

    renderCalendar(new Date(currentDate));
});
</script>
@endpush
