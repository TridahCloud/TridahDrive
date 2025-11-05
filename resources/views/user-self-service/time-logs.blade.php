@extends('layouts.dashboard')

@section('title', 'My Time Logs - ' . $drive->name)

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
                            <li class="breadcrumb-item active">My Time</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-clock me-2"></i>My Time Logs
                    </h1>
                    <p class="text-muted">{{ $person->full_name }} - {{ $drive->name }}</p>
                </div>
                <div>
                    <a href="{{ route('user-self-service.schedules', $drive) }}" class="btn btn-outline-primary">
                        <i class="fas fa-calendar-alt me-2"></i>View Schedules
                    </a>
                    <a href="{{ route('drives.show', $drive) }}" class="btn btn-outline-secondary">
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

    <!-- Filters -->
    <div class="dashboard-card mb-4">
        <form method="GET" action="{{ route('user-self-service.time-logs', $drive) }}" class="row g-3">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
                <a href="{{ route('user-self-service.time-logs', $drive) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Time Logs -->
    <div class="dashboard-card">
        @forelse($timeLogs as $timeLog)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <h5 class="mb-0">{{ $drive->formatForUser(\Carbon\Carbon::parse($timeLog->work_date), 'l, F j, Y', auth()->user()) }}</h5>
                                @if($timeLog->schedule)
                                    <span class="badge bg-info">{{ $timeLog->schedule->title ?? 'Scheduled Shift' }}</span>
                                @endif
                                <span class="badge bg-{{ $timeLog->status === 'approved' ? 'success' : ($timeLog->status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($timeLog->status) }}
                                </span>
                            </div>
                            
                            @if($timeLog->clock_in && $timeLog->clock_out)
                                <div class="mb-2">
                                    <i class="fas fa-clock me-2 text-muted"></i>
                                    <strong>Clock In:</strong> {{ $drive->formatForUser($timeLog->clock_in->copy()->setTimezone('UTC'), 'M j, g:i A', auth()->user()) }}
                                    <span class="ms-3"><strong>Clock Out:</strong> {{ $drive->formatForUser($timeLog->clock_out->copy()->setTimezone('UTC'), 'M j, g:i A', auth()->user()) }}</span>
                                    @php
                                        $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
                                        $scheduleTimezone = $timeLog->schedule->timezone ?? null;
                                    @endphp
                                    @if($scheduleTimezone && $scheduleTimezone !== $userTimezone)
                                        <br><small class="text-muted">Schedule timezone: {{ $scheduleTimezone }} | Displayed in: {{ $userTimezone }}</small>
                                    @endif
                                </div>
                            @elseif($timeLog->clock_in)
                                <div class="mb-2">
                                    <i class="fas fa-clock me-2 text-muted"></i>
                                    <strong>Clock In:</strong> {{ $drive->formatForUser($timeLog->clock_in->copy()->setTimezone('UTC'), 'M j, g:i A', auth()->user()) }}
                                    <span class="badge bg-warning ms-2">Still Clocked In</span>
                                    @php
                                        $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
                                        $scheduleTimezone = $timeLog->schedule->timezone ?? null;
                                    @endphp
                                    @if($scheduleTimezone && $scheduleTimezone !== $userTimezone)
                                        <br><small class="text-muted">Schedule timezone: {{ $scheduleTimezone }} | Displayed in: {{ $userTimezone }}</small>
                                    @endif
                                </div>
                            @endif
                            
                            <div class="mb-2">
                                <i class="fas fa-hourglass-half me-2 text-muted"></i>
                                <strong>Total Hours:</strong> {{ number_format($timeLog->total_hours ?? 0, 2) }}
                                @if($timeLog->regular_hours > 0 || $timeLog->overtime_hours > 0)
                                    <span class="text-muted ms-2">
                                        (Regular: {{ number_format($timeLog->regular_hours ?? 0, 2) }}, 
                                        Overtime: {{ number_format($timeLog->overtime_hours ?? 0, 2) }})
                                    </span>
                                @endif
                            </div>
                            
                            @if($timeLog->total_pay > 0)
                                <div class="mb-2">
                                    <i class="fas fa-dollar-sign me-2 text-muted"></i>
                                    <strong>Pay:</strong> {{ currency_for($timeLog->total_pay, $drive) }}
                                </div>
                            @endif
                            
                            @if($timeLog->notes)
                                <div class="mb-2">
                                    <i class="fas fa-sticky-note me-2 text-muted"></i>
                                    <small>{{ $timeLog->notes }}</small>
                                </div>
                            @endif
                            
                            @if($timeLog->approved_by && $timeLog->approval_notes)
                                <div class="alert alert-info mt-2 mb-0">
                                    <small>
                                        <strong>Approval Notes:</strong> {{ $timeLog->approval_notes }}
                                    </small>
                                </div>
                            @endif
                        </div>
                        
                        <div class="ms-3">
                            @if($timeLog->status !== 'approved')
                                <a href="{{ route('user-self-service.edit-time-log', [$drive, $timeLog]) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-edit me-2"></i>Edit
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No time logs found for the selected date range.
            </div>
        @endforelse
    </div>
</div>
@endsection

