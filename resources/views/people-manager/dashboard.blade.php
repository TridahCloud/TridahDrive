@extends('layouts.dashboard')

@section('title', 'People Manager Dashboard - ' . $drive->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('drives.index') }}">Drives</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('drives.show', $drive) }}">{{ $drive->name }}</a></li>
                            <li class="breadcrumb-item active">People Manager</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-users me-2"></i>People Manager Dashboard
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div class="d-flex gap-2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-2"></i>Manage
                        </button>
                        <ul class="dropdown-menu">
                            <li><h6 class="dropdown-header">Setup & Configuration</h6></li>
                            <li><a class="dropdown-item" href="{{ route('drives.people-manager.profiles.index', $drive) }}">
                                <i class="fas fa-user-cog me-2"></i>Profiles
                            </a></li>
                            <li><h6 class="dropdown-header">People & Resources</h6></li>
                            <li><a class="dropdown-item" href="{{ route('drives.people-manager.people.index', $drive) }}">
                                <i class="fas fa-users me-2"></i>People
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('drives.people-manager.schedules.index', $drive) }}">
                                <i class="fas fa-calendar-alt me-2"></i>Schedules
                            </a></li>
                        </ul>
                    </div>
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.people-manager.time-logs.index', $drive) }}" class="btn btn-info">
                            <i class="fas fa-clock me-2"></i>Time Logs
                            @if($stats['pending_time_logs'] > 0)
                                <span class="badge bg-danger ms-1">{{ $stats['pending_time_logs'] }}</span>
                            @endif
                        </a>
                        <a href="{{ route('drives.people-manager.payroll.index', $drive) }}" class="btn btn-success">
                            <i class="fas fa-money-check-alt me-2"></i>Payroll
                            @if($stats['unsynced_payroll'] > 0)
                                <span class="badge bg-warning ms-1">{{ $stats['unsynced_payroll'] }}</span>
                            @endif
                        </a>
                        <a href="{{ route('drives.people-manager.people.create', $drive) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add Person
                        </a>
                    @endif
                    <a href="{{ route('drives.show', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Drive
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

    @if(!$hasProfile)
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Get Started:</strong> Create a People Manager profile to configure your organization settings, payroll defaults, and more.
            <a href="{{ route('drives.people-manager.profiles.create', $drive) }}" class="alert-link">Create Profile</a>
        </div>
    @endif

    <!-- Stats Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="dashboard-card text-center">
                <h3 class="mb-0 text-primary">{{ $stats['total_people'] }}</h3>
                <p class="text-muted mb-0">Total People</p>
                <small class="text-muted">{{ $stats['active_people'] }} active</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card text-center">
                <h3 class="mb-0 text-info">{{ number_format($stats['total_hours_this_month'], 1) }}h</h3>
                <p class="text-muted mb-0">Hours This Month</p>
                <small class="text-muted">Approved time logs</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card text-center">
                <h3 class="mb-0 text-success">{{ currency_for($stats['total_payroll_this_month'], $drive) }}</h3>
                <p class="text-muted mb-0">Payroll This Month</p>
                <small class="text-muted">Paid entries</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card text-center">
                <h3 class="mb-0 text-warning">{{ $stats['total_schedules'] }}</h3>
                <p class="text-muted mb-0">Schedules This Month</p>
                <small class="text-muted">Current period</small>
            </div>
        </div>
    </div>

    <!-- People by Type -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="dashboard-card">
                <h5 class="mb-3"><i class="fas fa-chart-pie me-2"></i>People by Type</h5>
                <div class="row">
                    <div class="col-md-4 text-center">
                        <h4 class="text-primary">{{ $peopleByType['employee'] }}</h4>
                        <p class="text-muted mb-0">Employees</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <h4 class="text-info">{{ $peopleByType['contractor'] }}</h4>
                        <p class="text-muted mb-0">Contractors</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <h4 class="text-success">{{ $peopleByType['volunteer'] }}</h4>
                        <p class="text-muted mb-0">Volunteers</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Time Logs -->
        <div class="col-md-6">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Time Logs</h5>
                    <a href="{{ route('drives.people-manager.time-logs.index', $drive) }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                @if($recentTimeLogs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Person</th>
                                    <th>Date</th>
                                    <th>Hours</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTimeLogs as $log)
                                    <tr>
                                        <td>{{ $log->person->full_name }}</td>
                                        <td>{{ $log->work_date->format('M d, Y') }}</td>
                                        <td>{{ number_format($log->total_hours, 1) }}h</td>
                                        <td>
                                            <span class="badge bg-{{ $log->status === 'approved' ? 'success' : ($log->status === 'pending' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($log->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-3">No time logs yet.</p>
                @endif
            </div>
        </div>

        <!-- Upcoming Schedules -->
        <div class="col-md-6">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Upcoming Schedules</h5>
                    <a href="{{ route('drives.people-manager.schedules.index', $drive) }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                @if($upcomingSchedules->count() > 0)
                    <div class="list-group">
                        @foreach($upcomingSchedules as $schedule)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $schedule->title }}</h6>
                                        <p class="mb-1 text-muted">
                                            <i class="fas fa-user me-1"></i>{{ $schedule->person->full_name ?? 'Unassigned' }}
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>{{ $schedule->start_date->format('M d, Y') }}
                                            @if($schedule->start_time)
                                                <i class="fas fa-clock ms-2 me-1"></i>{{ \Carbon\Carbon::parse($schedule->getStartTimeForUser(auth()->user()))->format('g:i A') }}
                                                @if($schedule->end_time)
                                                    - {{ \Carbon\Carbon::parse($schedule->getEndTimeForUser(auth()->user()))->format('g:i A') }}
                                                @endif
                                            @endif
                                        </small>
                                    </div>
                                    <span class="badge bg-{{ $schedule->status === 'confirmed' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($schedule->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-3">No upcoming schedules.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
