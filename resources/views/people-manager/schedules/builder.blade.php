@extends('layouts.dashboard')

@section('title', 'Schedule Builder - ' . $drive->name)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('drives.index') }}">Drives</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('drives.show', $drive) }}">{{ $drive->name }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('drives.people-manager.dashboard', $drive) }}">People Manager</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('drives.people-manager.schedules.index', $drive) }}">Schedules</a></li>
                            <li class="breadcrumb-item active">Builder</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal"><i class="fas fa-calendar-alt me-2"></i>Schedule Builder</h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div>
                    <a href="{{ route('drives.people-manager.schedules.index', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Week Navigation and Controls -->
    <div class="dashboard-card mb-4">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('drives.people-manager.schedules.builder', [$drive, 'week_start' => $weekStart->copy()->subWeek()->format('Y-m-d')]) }}" 
                       class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-chevron-left"></i> Previous Week
                    </a>
                    <strong>{{ $weekStart->format('M d') }} - {{ $weekEnd->format('M d, Y') }}</strong>
                    <a href="{{ route('drives.people-manager.schedules.builder', [$drive, 'week_start' => $weekStart->copy()->addWeek()->format('Y-m-d')]) }}" 
                       class="btn btn-sm btn-outline-secondary">
                        Next Week <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="{{ route('drives.people-manager.schedules.builder', $drive) }}" 
                       class="btn btn-sm btn-outline-secondary">
                        This Week
                    </a>
                </div>
            </div>
            <div class="col-md-8">
                <div class="d-flex gap-2 justify-content-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="quickFillBtn">
                            <i class="fas fa-magic me-1"></i>Quick Fill
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info" id="clearAllBtn">
                            <i class="fas fa-eraser me-1"></i>Clear All
                        </button>
                    </div>
                    @if($drive->canEdit(auth()->user()))
                        <button type="button" class="btn btn-sm btn-success" id="saveScheduleBtn">
                            <i class="fas fa-save me-1"></i>Save Schedule
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Live Statistics -->
    <div class="dashboard-card mb-4">
        <div class="row text-center">
            <div class="col-md-3">
                <div class="stat-item">
                    <h3 class="mb-0 text-primary" id="totalPeople">0</h3>
                    <small class="text-muted">People Scheduled</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    <h3 class="mb-0 text-success" id="totalDays">0</h3>
                    <small class="text-muted">Days Covered</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    <h3 class="mb-0 text-info" id="totalHours">0</h3>
                    <small class="text-muted">Total Hours</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    <h3 class="mb-0 text-warning" id="totalShifts">0</h3>
                    <small class="text-muted">Total Shifts</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Scheduling Helper Tool -->
    <div class="dashboard-card mb-4 collapse" id="quickFillPanel">
        <h5 class="mb-3">Quick Fill Options</h5>
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Fill Hours</label>
                <input type="number" step="0.5" class="form-control" id="fillHours" placeholder="8" min="0.5" max="24">
            </div>
            <div class="col-md-3">
                <label class="form-label">Start Time</label>
                <input type="time" class="form-control" id="fillStartTime" value="09:00">
            </div>
            <div class="col-md-3">
                <label class="form-label">Days</label>
                <select class="form-select" id="fillDays" multiple>
                    <option value="0">Monday</option>
                    <option value="1">Tuesday</option>
                    <option value="2">Wednesday</option>
                    <option value="3">Thursday</option>
                    <option value="4">Friday</option>
                    <option value="5">Saturday</option>
                    <option value="6">Sunday</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-primary w-100" id="applyQuickFill">
                    <i class="fas fa-magic me-1"></i>Apply
                </button>
            </div>
        </div>
    </div>

    <!-- Schedule Table -->
    <div class="dashboard-card">
        <div class="table-responsive">
            <table class="table table-bordered schedule-table" id="scheduleTable">
                <thead>
                    <tr>
                        <th style="width: 200px;">Person</th>
                        @foreach($daysOfWeek as $day)
                            <th class="text-center">
                                <div>{{ $day->format('D') }}</div>
                                <div class="small text-muted">{{ $day->format('M d') }}</div>
                            </th>
                        @endforeach
                        <th class="text-center" style="width: 100px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($people as $person)
                        <tr data-person-id="{{ $person->id }}">
                            <td class="person-cell">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <strong>{{ $person->full_name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <span class="badge bg-info">{{ ucfirst($person->type) }}</span>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            @foreach($daysOfWeek as $dayIndex => $day)
                                <td class="day-cell" 
                                    data-person-id="{{ $person->id }}" 
                                    data-date="{{ $day->format('Y-m-d') }}"
                                    data-day-index="{{ $dayIndex }}">
                                    <div class="schedule-cell" 
                                         data-person-id="{{ $person->id }}" 
                                         data-date="{{ $day->format('Y-m-d') }}">
                                        @if(isset($scheduleData[$person->id][$day->format('Y-m-d')]))
                                            @foreach($scheduleData[$person->id][$day->format('Y-m-d')] as $schedule)
                                                <div class="schedule-block" 
                                                     data-schedule-id="{{ $schedule->id }}"
                                                     data-start-time="{{ $schedule->start_time }}"
                                                     data-end-time="{{ $schedule->end_time }}">
                                                    <button type="button" class="delete-schedule-icon" 
                                                            data-schedule-id="{{ $schedule->id }}"
                                                            title="Delete schedule">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <small>
                                                        {{ \Carbon\Carbon::parse($schedule->getStartTimeForUser(auth()->user()))->format('g:i A') }} - 
                                                        {{ \Carbon\Carbon::parse($schedule->getEndTimeForUser(auth()->user()))->format('g:i A') }}
                                                    </small>
                                                    <br>
                                                    <strong>{{ number_format($schedule->total_hours, 1) }}h</strong>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <div class="cell-total small text-muted text-center mt-1" data-person-id="{{ $person->id }}" data-date="{{ $day->format('Y-m-d') }}">
                                        0h
                                    </div>
                                </td>
                            @endforeach
                            <td class="text-center person-total" data-person-id="{{ $person->id }}">
                                <strong>0h</strong>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Time Selection Modal -->
<div class="modal fade" id="timeSelectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Schedule Time</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalPersonId">
                <input type="hidden" id="modalDate">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="modalStartTime" class="form-label">Start Time</label>
                        <input type="time" class="form-control" id="modalStartTime" value="09:00">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="modalEndTime" class="form-label">End Time</label>
                        <input type="time" class="form-control" id="modalEndTime" value="17:00">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="modalTitle" class="form-label">Title (Optional)</label>
                    <input type="text" class="form-control" id="modalTitle" placeholder="Shift">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteScheduleBtn" style="display: none;">Delete</button>
                <button type="button" class="btn btn-primary" id="saveTimeBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<style>
.schedule-table {
    font-size: 0.9rem;
}

.schedule-table th {
    background-color: #f8f9fa;
    position: sticky;
    top: 0;
    z-index: 10;
}

.person-cell {
    background-color: #f8f9fa;
    position: sticky;
    left: 0;
    z-index: 5;
}

.schedule-cell {
    min-height: 60px;
    position: relative;
    cursor: pointer;
    border: 1px dashed #dee2e6;
    padding: 4px;
    transition: background-color 0.2s;
}

.schedule-cell:hover {
    background-color: #f8f9fa;
}

.schedule-block {
    background-color: #0d6efd;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    margin-bottom: 4px;
    font-size: 0.8rem;
    cursor: pointer;
    position: relative;
}

.schedule-block:hover {
    background-color: #0b5ed7;
}

.delete-schedule-icon {
    position: absolute;
    top: 2px;
    right: 2px;
    background: rgba(220, 53, 69, 0.9);
    border: none;
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 3px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    opacity: 0.8;
    transition: all 0.2s;
    font-size: 11px;
    z-index: 100;
    padding: 0;
    line-height: 1;
}

.schedule-block:hover .delete-schedule-icon {
    opacity: 1;
    background: rgba(220, 53, 69, 1);
}

.delete-schedule-icon:hover {
    background: rgba(220, 53, 69, 1) !important;
    opacity: 1 !important;
    transform: scale(1.15);
}

.stat-item {
    padding: 15px;
}

.stat-item h3 {
    font-weight: bold;
}

.day-cell {
    min-width: 120px;
    vertical-align: top;
}

.cell-total {
    font-weight: bold;
}

.person-total {
    font-weight: bold;
    background-color: #f8f9fa;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scheduleData = @json($scheduleData);
    let currentAssignments = {};
    let draggedCell = null;
    let isDragging = false;

    // Initialize current assignments from existing schedules
    @foreach($people as $person)
        @foreach($daysOfWeek as $day)
            @if(isset($scheduleData[$person->id][$day->format('Y-m-d')]))
                @foreach($scheduleData[$person->id][$day->format('Y-m-d')] as $schedule)
                    const key{{ $person->id }}_{{ $day->format('Ymd') }} = '{{ $person->id }}_{{ $day->format('Y-m-d') }}';
                    if (!currentAssignments[key{{ $person->id }}_{{ $day->format('Ymd') }}]) {
                        currentAssignments[key{{ $person->id }}_{{ $day->format('Ymd') }}] = [];
                    }
                    currentAssignments[key{{ $person->id }}_{{ $day->format('Ymd') }}].push({
                        id: {{ $schedule->id }},
                        start_time: '{{ $schedule->start_time }}',
                        end_time: '{{ $schedule->end_time }}',
                        hours: {{ $schedule->total_hours }}
                    });
                @endforeach
            @endif
        @endforeach
    @endforeach

    // Click handler for schedule cells and blocks
    document.querySelectorAll('.schedule-cell').forEach(cell => {
        cell.addEventListener('click', function(e) {
            // Don't handle if clicking delete button or icon
            if (e.target.classList.contains('delete-schedule') || 
                e.target.closest('.delete-schedule-icon')) {
                return;
            }
            
            // Get person and date from cell
            const personId = String(this.dataset.personId);
            const date = this.dataset.date;
            const key = `${personId}_${date}`;
            let existing = null;
            
            const modal = new bootstrap.Modal(document.getElementById('timeSelectionModal'));
            
            document.getElementById('modalPersonId').value = personId;
            document.getElementById('modalDate').value = date;
            
            // FIRST: Check the DOM for schedule blocks with IDs (actual saved schedules)
            // Check if click was on a schedule block itself
            const clickedBlock = e.target.closest('.schedule-block');
            let scheduleBlock = clickedBlock || this.querySelector('.schedule-block');
            
            if (scheduleBlock && scheduleBlock.dataset.scheduleId) {
                // Get schedule details from the rendered block
                const startTime = scheduleBlock.dataset.startTime;
                const endTime = scheduleBlock.dataset.endTime;
                
                // Convert time format if needed (handle HH:MM:SS format)
                let startTimeFormatted = startTime;
                let endTimeFormatted = endTime;
                if (startTime.includes(':')) {
                    startTimeFormatted = startTime.split(':').slice(0, 2).join(':');
                }
                if (endTime.includes(':')) {
                    endTimeFormatted = endTime.split(':').slice(0, 2).join(':');
                }
                
                const scheduleId = parseInt(scheduleBlock.dataset.scheduleId);
                
                existing = {
                    id: scheduleId,
                    start_time: startTimeFormatted,
                    end_time: endTimeFormatted
                };
                
                // Update currentAssignments with this info
                if (!currentAssignments[key]) {
                    currentAssignments[key] = [];
                }
                if (!currentAssignments[key].find(a => a.id == scheduleId)) {
                    const start = new Date(`2000-01-01 ${startTimeFormatted}`);
                    const end = new Date(`2000-01-01 ${endTimeFormatted}`);
                    const hours = (end - start) / (1000 * 60 * 60);
                    currentAssignments[key].push({
                        id: scheduleId,
                        start_time: startTimeFormatted,
                        end_time: endTimeFormatted,
                        hours: hours
                    });
                }
                // Use the one from DOM, not from currentAssignments (which might not have ID)
                existing = {
                    id: scheduleId,
                    start_time: startTimeFormatted,
                    end_time: endTimeFormatted
                };
            } else {
                // If no DOM schedule block, check currentAssignments (for unsaved edits)
                if (currentAssignments[key] && currentAssignments[key].length > 0) {
                    existing = currentAssignments[key][0];
                }
            }
            
            // Show/hide delete button and set schedule ID
            const deleteBtn = document.getElementById('deleteScheduleBtn');
            
            if (existing && existing.id) {
                // Existing saved schedule - show delete button
                const startTime = new Date('2000-01-01 ' + existing.start_time);
                const endTime = new Date('2000-01-01 ' + existing.end_time);
                document.getElementById('modalStartTime').value = startTime.toTimeString().slice(0,5);
                document.getElementById('modalEndTime').value = endTime.toTimeString().slice(0,5);
                deleteBtn.style.display = 'inline-block';
                deleteBtn.dataset.scheduleId = existing.id;
            } else if (existing) {
                // Existing but unsaved (no ID) - don't show delete button
                const startTime = new Date('2000-01-01 ' + existing.start_time);
                const endTime = new Date('2000-01-01 ' + existing.end_time);
                document.getElementById('modalStartTime').value = startTime.toTimeString().slice(0,5);
                document.getElementById('modalEndTime').value = endTime.toTimeString().slice(0,5);
                deleteBtn.style.display = 'none';
                deleteBtn.removeAttribute('data-schedule-id');
            } else {
                // No existing schedule - default times, no delete button
                document.getElementById('modalStartTime').value = '09:00';
                document.getElementById('modalEndTime').value = '17:00';
                deleteBtn.style.display = 'none';
                deleteBtn.removeAttribute('data-schedule-id');
            }
            
            modal.show();
        });
    });

    // Save time from modal
    document.getElementById('saveTimeBtn').addEventListener('click', function() {
        const personId = document.getElementById('modalPersonId').value;
        const date = document.getElementById('modalDate').value;
        const startTime = document.getElementById('modalStartTime').value;
        const endTime = document.getElementById('modalEndTime').value;
        const title = document.getElementById('modalTitle').value || 'Shift';
        
        const key = `${personId}_${date}`;
        
        // Check if there's an existing schedule ID to preserve
        let existingId = null;
        const cell = document.querySelector(`.schedule-cell[data-person-id="${personId}"][data-date="${date}"]`);
        if (cell) {
            const existingBlock = cell.querySelector('.schedule-block[data-schedule-id]');
            if (existingBlock) {
                existingId = parseInt(existingBlock.dataset.scheduleId);
            }
        }
        
        // Also check currentAssignments for existing ID
        if (!existingId && currentAssignments[key] && currentAssignments[key].length > 0) {
            const existingAssignment = currentAssignments[key].find(a => a.id);
            if (existingAssignment) {
                existingId = existingAssignment.id;
            }
        }
        
        if (!currentAssignments[key]) {
            currentAssignments[key] = [];
        }
        
        // Calculate hours
        const start = new Date(`2000-01-01 ${startTime}`);
        const end = new Date(`2000-01-01 ${endTime}`);
        const hours = (end - start) / (1000 * 60 * 60);
        
        // Replace existing or add new - preserve ID if it exists
        currentAssignments[key] = [{
            id: existingId || undefined, // Only set if it exists
            start_time: startTime,
            end_time: endTime,
            hours: hours,
            title: title
        }];
        
        updateCellDisplay(personId, date);
        updateStatistics();
        
        bootstrap.Modal.getInstance(document.getElementById('timeSelectionModal')).hide();
    });

    // Delete schedule icon click handler (delegated event listener)
    document.addEventListener('click', function(e) {
        const deleteIcon = e.target.closest('.delete-schedule-icon');
        if (deleteIcon) {
            e.stopPropagation(); // Prevent cell click handler
            e.preventDefault();
            
            const scheduleId = deleteIcon.dataset.scheduleId;
            const tempKey = deleteIcon.dataset.tempKey;
            
            // Find the cell to get person and date
            const cell = deleteIcon.closest('.schedule-cell');
            const personId = cell ? cell.dataset.personId : null;
            const date = cell ? cell.dataset.date : null;
            const key = personId && date ? `${personId}_${date}` : (tempKey || null);
            
            if (scheduleId) {
                // Delete saved schedule from server
                if (!confirm('Are you sure you want to delete this schedule?')) {
                    return;
                }
                
                const deleteUrl = `{{ route('drives.people-manager.schedules.index', $drive) }}/${scheduleId}`;
                
                fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                }).then(response => {
                    if (response.ok || response.status === 204) {
                        if (key) {
                            delete currentAssignments[key];
                            updateCellDisplay(personId, date);
                            updateStatistics();
                        }
                        location.reload(); // Reload to show updated state
                    } else {
                        return response.text().then(text => {
                            console.error('Delete failed:', response.status, text);
                            alert('Failed to delete schedule. Status: ' + response.status);
                        });
                    }
                }).catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting schedule: ' + error.message);
                });
            } else if (key) {
                // Remove temporary/unsaved schedule
                delete currentAssignments[key];
                updateCellDisplay(personId, date);
                updateStatistics();
            }
        }
    });

    // Delete schedule
    document.getElementById('deleteScheduleBtn').addEventListener('click', function() {
        const scheduleId = this.dataset.scheduleId;
        const personId = document.getElementById('modalPersonId').value;
        const date = document.getElementById('modalDate').value;
        const key = `${personId}_${date}`;
        
        if (scheduleId) {
            // Delete from server using the correct route
            const deleteUrl = `{{ route('drives.people-manager.schedules.index', $drive) }}/${scheduleId}`;
            
            fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            }).then(response => {
                if (response.ok || response.status === 204) {
                    delete currentAssignments[key];
                    updateCellDisplay(personId, date);
                    updateStatistics();
                    bootstrap.Modal.getInstance(document.getElementById('timeSelectionModal')).hide();
                    location.reload(); // Reload to show updated state
                } else {
                    return response.text().then(text => {
                        console.error('Delete failed:', response.status, text);
                        alert('Failed to delete schedule. Status: ' + response.status);
                    });
                }
            }).catch(error => {
                console.error('Error:', error);
                alert('Error deleting schedule: ' + error.message);
            });
        } else {
            // Just remove from local assignments (unsaved)
            delete currentAssignments[key];
            updateCellDisplay(personId, date);
            updateStatistics();
            bootstrap.Modal.getInstance(document.getElementById('timeSelectionModal')).hide();
        }
    });

    // Update cell display
    function updateCellDisplay(personId, date) {
        const key = `${personId}_${date}`;
        const cell = document.querySelector(`.schedule-cell[data-person-id="${personId}"][data-date="${date}"]`);
        const cellTotal = document.querySelector(`.cell-total[data-person-id="${personId}"][data-date="${date}"]`);
        
        if (!cell) return;
        
        // First, check if there are server-rendered blocks with IDs that we should preserve
        const existingBlocks = cell.querySelectorAll('.schedule-block[data-schedule-id]');
        const existingIds = Array.from(existingBlocks).map(block => parseInt(block.dataset.scheduleId));
        
        // Clear only if we're updating with new data
        cell.innerHTML = '';
        let totalHours = 0;
        
        if (currentAssignments[key] && currentAssignments[key].length > 0) {
            currentAssignments[key].forEach(assignment => {
                const startTime = new Date(`2000-01-01 ${assignment.start_time}`);
                const endTime = new Date(`2000-01-01 ${assignment.end_time}`);
                const startStr = startTime.toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit'});
                const endStr = endTime.toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit'});
                
                const block = document.createElement('div');
                block.className = 'schedule-block';
                
                // ALWAYS add delete icon for ANY schedule block (saved or temporary)
                const deleteIcon = document.createElement('button');
                deleteIcon.type = 'button';
                deleteIcon.className = 'delete-schedule-icon';
                deleteIcon.title = 'Remove schedule';
                deleteIcon.innerHTML = '<i class="fas fa-times"></i>';
                
                // Set schedule ID if it exists (for saved schedules)
                if (assignment.id) {
                    block.dataset.scheduleId = assignment.id;
                    block.dataset.startTime = assignment.start_time;
                    block.dataset.endTime = assignment.end_time;
                    deleteIcon.dataset.scheduleId = assignment.id;
                } else if (existingIds.length > 0) {
                    // Preserve ID from existing blocks if available
                    block.dataset.scheduleId = existingIds[0];
                    block.dataset.startTime = assignment.start_time;
                    block.dataset.endTime = assignment.end_time;
                    deleteIcon.dataset.scheduleId = existingIds[0];
                } else {
                    // Temporary schedule - store assignment key for removal
                    deleteIcon.dataset.tempKey = key;
                }
                
                block.appendChild(deleteIcon);
                
                // Add content
                const content = document.createElement('div');
                content.innerHTML = `
                    <small>${startStr} - ${endStr}</small><br>
                    <strong>${assignment.hours.toFixed(1)}h</strong>
                `;
                block.appendChild(content);
                cell.appendChild(block);
                totalHours += assignment.hours;
            });
        }
        
        if (cellTotal) {
            cellTotal.textContent = totalHours > 0 ? totalHours.toFixed(1) + 'h' : '0h';
        }
        
        updatePersonTotal(personId);
    }

    // Update person total
    function updatePersonTotal(personId) {
        const personTotal = document.querySelector(`.person-total[data-person-id="${personId}"]`);
        if (!personTotal) return;
        
        let total = 0;
        @foreach($daysOfWeek as $day)
            const key{{ $day->format('Ymd') }} = `${personId}_{{ $day->format('Y-m-d') }}`;
            if (currentAssignments[key{{ $day->format('Ymd') }}]) {
                currentAssignments[key{{ $day->format('Ymd') }}].forEach(a => total += a.hours);
            }
        @endforeach
        
        personTotal.innerHTML = `<strong>${total.toFixed(1)}h</strong>`;
    }

    // Update statistics
    function updateStatistics() {
        const peopleSet = new Set();
        const daysSet = new Set();
        let totalHours = 0;
        let totalShifts = 0;
        
        Object.keys(currentAssignments).forEach(key => {
            const [personId, date] = key.split('_');
            if (currentAssignments[key] && currentAssignments[key].length > 0) {
                peopleSet.add(personId);
                daysSet.add(date);
                currentAssignments[key].forEach(assignment => {
                    totalHours += assignment.hours;
                    totalShifts++;
                });
            }
        });
        
        document.getElementById('totalPeople').textContent = peopleSet.size;
        document.getElementById('totalDays').textContent = daysSet.size;
        document.getElementById('totalHours').textContent = totalHours.toFixed(1);
        document.getElementById('totalShifts').textContent = totalShifts;
    }

    // Quick fill
    document.getElementById('quickFillBtn').addEventListener('click', function() {
        const panel = document.getElementById('quickFillPanel');
        const bsCollapse = new bootstrap.Collapse(panel);
        bsCollapse.toggle();
    });

    document.getElementById('applyQuickFill').addEventListener('click', function() {
        const hours = parseFloat(document.getElementById('fillHours').value);
        const startTime = document.getElementById('fillStartTime').value;
        const selectedDays = Array.from(document.getElementById('fillDays').selectedOptions).map(o => parseInt(o.value));
        
        if (!hours || selectedDays.length === 0) {
            alert('Please select hours and at least one day');
            return;
        }
        
        const start = new Date(`2000-01-01 ${startTime}`);
        const end = new Date(start.getTime() + (hours * 60 * 60 * 1000));
        const endTimeStr = end.toTimeString().slice(0,5);
        
        const daysOfWeekDates = [
            @foreach($daysOfWeek as $day)
                '{{ $day->format('Y-m-d') }}',
            @endforeach
        ];
        
        @foreach($people as $person)
            selectedDays.forEach(dayIndex => {
                if (daysOfWeekDates[dayIndex]) {
                    const personId = {{ $person->id }};
                    const date = daysOfWeekDates[dayIndex];
                    const key = `${personId}_${date}`;
                    if (!currentAssignments[key]) {
                        currentAssignments[key] = [];
                    }
                    currentAssignments[key] = [{
                        start_time: startTime,
                        end_time: endTimeStr,
                        hours: hours,
                        title: 'Shift'
                    }];
                    updateCellDisplay(personId, date);
                }
            });
        @endforeach
        
        updateStatistics();
    });

    // Clear all
    document.getElementById('clearAllBtn').addEventListener('click', function() {
        if (confirm('Are you sure you want to clear all unsaved assignments?')) {
            currentAssignments = {};
            @foreach($people as $person)
                @foreach($daysOfWeek as $day)
                    updateCellDisplay({{ $person->id }}, '{{ $day->format('Y-m-d') }}');
                @endforeach
            @endforeach
            updateStatistics();
        }
    });

    // Save schedule
    @if($drive->canEdit(auth()->user()))
    document.getElementById('saveScheduleBtn').addEventListener('click', function() {
        const assignments = [];
        
        Object.keys(currentAssignments).forEach(key => {
            const [personId, date] = key.split('_');
            if (currentAssignments[key] && currentAssignments[key].length > 0) {
                currentAssignments[key].forEach(assignment => {
                    // Check if this is an existing schedule (has id) or new
                    if (!assignment.id) {
                        assignments.push({
                            person_id: personId,
                            date: date,
                            start_time: assignment.start_time,
                            end_time: assignment.end_time,
                            title: assignment.title || 'Shift'
                        });
                    }
                });
            }
        });
        
        if (assignments.length === 0) {
            alert('No new assignments to save');
            return;
        }
        
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
        
        fetch('{{ route('drives.people-manager.schedules.bulk-create', $drive) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ assignments: assignments })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to save schedules'));
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-save me-1"></i>Save Schedule';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving schedules');
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-save me-1"></i>Save Schedule';
        });
    });
    @endif

    // Initialize statistics
    updateStatistics();
});
</script>
@endsection

