@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.js.min.css">
<style>
    .workload-chart-container {
        position: relative;
        height: 400px;
        margin-bottom: 2rem;
    }
    .workload-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .workload-stat-card {
        background-color: var(--bg-secondary);
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
    }
    .workload-stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: var(--brand-teal);
    }
</style>
@endpush

<div class="row">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Workload View</h5>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary active" onclick="showWorkloadView('member')">
                        <i class="fas fa-users me-1"></i>By Member
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="showWorkloadView('status')">
                        <i class="fas fa-tasks me-1"></i>By Status
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="showWorkloadView('priority')">
                        <i class="fas fa-exclamation-circle me-1"></i>By Priority
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="workload-stats">
                <div class="workload-stat-card">
                    <div class="workload-stat-value">{{ $project->tasks->whereNull('deleted_at')->count() }}</div>
                    <div class="text-muted">Total Tasks</div>
                </div>
                <div class="workload-stat-card">
                    <div class="workload-stat-value">{{ $project->tasks->whereNull('deleted_at')->where('status', 'done')->count() }}</div>
                    <div class="text-muted">Completed</div>
                </div>
                <div class="workload-stat-card">
                    <div class="workload-stat-value">{{ $project->tasks->whereNull('deleted_at')->where('status', 'in_progress')->count() }}</div>
                    <div class="text-muted">In Progress</div>
                </div>
                <div class="workload-stat-card">
                    <div class="workload-stat-value">{{ $driveMembers->count() }}</div>
                    <div class="text-muted">Team Members</div>
                </div>
            </div>

            <!-- Workload by Member -->
            <div id="workload-member" class="workload-view">
                <h6 class="mb-3">Task Distribution by Member</h6>
                <div class="workload-chart-container">
                    <canvas id="memberWorkloadChart"></canvas>
                </div>
                
                <div class="table-responsive mt-4">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Total Tasks</th>
                                <th>In Progress</th>
                                <th>Done</th>
                                <th>Overdue</th>
                                <th>Estimated Hours</th>
                            </tr>
                        </thead>
                        <tbody>
            @php
                $memberStatsArray = isset($memberStats) && is_array($memberStats) ? $memberStats : [];
            @endphp
            @forelse($memberStatsArray as $stats)
                                <tr>
                                    <td><strong>{{ $stats['name'] }}</strong></td>
                                    <td><span class="badge bg-primary">{{ $stats['total'] }}</span></td>
                                    <td><span class="badge bg-info">{{ $stats['in_progress'] }}</span></td>
                                    <td><span class="badge bg-success">{{ $stats['done'] }}</span></td>
                                    <td>
                                        @if($stats['overdue'] > 0)
                                            <span class="badge bg-danger">{{ $stats['overdue'] }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td>{{ $stats['estimated_hours'] }}h</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">
                                        No team members assigned to tasks yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Workload by Status -->
            <div id="workload-status" class="workload-view" style="display: none;">
                <h6 class="mb-3">Tasks by Status</h6>
                <div class="workload-chart-container">
                    <canvas id="statusWorkloadChart"></canvas>
                </div>
            </div>

            <!-- Workload by Priority -->
            <div id="workload-priority" class="workload-view" style="display: none;">
                <h6 class="mb-3">Tasks by Priority</h6>
                <div class="workload-chart-container">
                    <canvas id="priorityWorkloadChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let memberChart, statusChart, priorityChart;

function showWorkloadView(view) {
    // Hide all views
    document.querySelectorAll('.workload-view').forEach(el => el.style.display = 'none');
    
    // Show selected view
    document.getElementById('workload-' + view).style.display = 'block';
    
    // Update button states
    document.querySelectorAll('.btn-group button').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') {
        // Member Workload Chart
        const memberCtx = document.getElementById('memberWorkloadChart');
        if (memberCtx) {
            @php
                $memberLabels = !empty($memberStatsArray) ? array_column($memberStatsArray, 'name') : [];
                $memberTotals = !empty($memberStatsArray) ? array_column($memberStatsArray, 'total') : [];
            @endphp
            
            const memberData = {
                labels: @json($memberLabels),
                datasets: [{
                    label: 'Total Tasks',
                    data: @json($memberTotals),
                    backgroundColor: 'rgba(49, 216, 178, 0.6)',
                    borderColor: 'rgba(49, 216, 178, 1)',
                    borderWidth: 2
                }]
            };
            
            if (memberData.labels.length > 0) {
                memberChart = new Chart(memberCtx, {
                type: 'bar',
                data: memberData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Tasks: ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
            } else {
                memberCtx.closest('.workload-chart-container').innerHTML = 
                    '<div class="alert alert-info text-center py-4">No data available. Assign tasks to team members to see workload distribution.</div>';
            }
        }

        // Status Workload Chart
        const statusCtx = document.getElementById('statusWorkloadChart');
        if (statusCtx) {
            const statusData = {
                labels: ['Todo', 'In Progress', 'Review', 'Done', 'Blocked'],
                datasets: [{
                    data: [
                        {{ $tasksByStatus['todo']->count() }},
                        {{ $tasksByStatus['in_progress']->count() }},
                        {{ $tasksByStatus['review']->count() }},
                        {{ $tasksByStatus['done']->count() }},
                        {{ $tasksByStatus['blocked']->count() }}
                    ],
                    backgroundColor: [
                        'rgba(108, 117, 125, 0.6)',
                        'rgba(13, 110, 253, 0.6)',
                        'rgba(13, 202, 240, 0.6)',
                        'rgba(25, 135, 84, 0.6)',
                        'rgba(220, 53, 69, 0.6)'
                    ],
                    borderColor: [
                        'rgba(108, 117, 125, 1)',
                        'rgba(13, 110, 253, 1)',
                        'rgba(13, 202, 240, 1)',
                        'rgba(25, 135, 84, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 2
                }]
            };
            
            statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: statusData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }

        // Priority Workload Chart
        const priorityCtx = document.getElementById('priorityWorkloadChart');
        if (priorityCtx) {
            @php
                $priorityCounts = [
                    'low' => $project->tasks->whereNull('deleted_at')->where('priority', 'low')->count(),
                    'medium' => $project->tasks->whereNull('deleted_at')->where('priority', 'medium')->count(),
                    'high' => $project->tasks->whereNull('deleted_at')->where('priority', 'high')->count(),
                    'urgent' => $project->tasks->whereNull('deleted_at')->where('priority', 'urgent')->count(),
                ];
            @endphp
            
            const priorityData = {
                labels: ['Low', 'Medium', 'High', 'Urgent'],
                datasets: [{
                    data: [
                        {{ $priorityCounts['low'] }},
                        {{ $priorityCounts['medium'] }},
                        {{ $priorityCounts['high'] }},
                        {{ $priorityCounts['urgent'] }}
                    ],
                    backgroundColor: [
                        'rgba(108, 117, 125, 0.6)',
                        'rgba(13, 202, 240, 0.6)',
                        'rgba(255, 193, 7, 0.6)',
                        'rgba(220, 53, 69, 0.6)'
                    ],
                    borderColor: [
                        'rgba(108, 117, 125, 1)',
                        'rgba(13, 202, 240, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 2
                }]
            };
            
            priorityChart = new Chart(priorityCtx, {
                type: 'pie',
                data: priorityData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }
    } else {
        // Fallback if Chart.js fails to load
        document.querySelectorAll('.workload-chart-container').forEach(container => {
            container.innerHTML = '<div class="alert alert-warning">Chart library failed to load. Please refresh the page.</div>';
        });
    }
});
</script>
@endpush
