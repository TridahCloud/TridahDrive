@extends('layouts.dashboard')

@section('title', 'My Schedules - ' . $drive->name)

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
                        <i class="fas fa-calendar-alt me-2"></i>My Schedules
                    </h1>
                    <p class="text-muted">{{ $person->full_name }} - {{ $drive->name }}</p>
                </div>
                <div>
                    <a href="{{ route('user-self-service.time-logs', $drive) }}" class="btn btn-outline-primary">
                        <i class="fas fa-clock me-2"></i>View Time Logs
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Upcoming Schedules -->
    <div class="dashboard-card mb-4">
        <h4 class="mb-3 brand-teal">
            <i class="fas fa-calendar-check me-2"></i>Upcoming Shifts
        </h4>
        
        @forelse($upcomingSchedules as $schedule)
            @php
                $scheduleDate = \Carbon\Carbon::parse($schedule->start_date);
                $isToday = $scheduleDate->isToday();
                $isTomorrow = $scheduleDate->isTomorrow();
                
                // Check if there's a time log for this schedule on the schedule date
                $timeLog = $drive->timeLogs()
                    ->where('person_id', $person->id)
                    ->where('schedule_id', $schedule->id)
                    ->where('work_date', $scheduleDate->format('Y-m-d'))
                    ->first();
                
                $hasClockedIn = $timeLog && $timeLog->clock_in;
                $hasClockedOut = $timeLog && $timeLog->clock_out;
            @endphp
            
            <div class="card mb-3 {{ $isToday ? 'border-primary' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <h5 class="mb-0">{{ $schedule->title ?? 'Shift' }}</h5>
                                @if($isToday)
                                    <span class="badge bg-primary">Today</span>
                                @elseif($isTomorrow)
                                    <span class="badge bg-info">Tomorrow</span>
                                @endif
                                @if($schedule->status === 'confirmed')
                                    <span class="badge bg-success">Confirmed</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($schedule->status) }}</span>
                                @endif
                            </div>
                            
                            <div class="mb-2">
                                <i class="fas fa-calendar me-2 text-muted"></i>
                                <strong>{{ $scheduleDate->format('l, F j, Y') }}</strong>
                            </div>
                            
                            <div class="mb-2">
                                <i class="fas fa-clock me-2 text-muted"></i>
                                {{ \Carbon\Carbon::parse($schedule->getStartTimeForUser(auth()->user()))->format('g:i A') }} - 
                                {{ \Carbon\Carbon::parse($schedule->getEndTimeForUser(auth()->user()))->format('g:i A') }}
                                @if($schedule->total_hours)
                                    <span class="text-muted">({{ number_format($schedule->total_hours, 2) }} hours)</span>
                                @endif
                            </div>
                            
                            @if($schedule->location)
                                <div class="mb-2">
                                    <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                                    {{ $schedule->location }}
                                </div>
                            @endif
                            
                            @if($schedule->notes)
                                <div class="mb-2">
                                    <i class="fas fa-sticky-note me-2 text-muted"></i>
                                    <small>{{ $schedule->notes }}</small>
                                </div>
                            @endif
                            
                            @if($timeLog)
                                <div class="mt-3">
                                    @if($hasClockedIn)
                                        <div class="alert alert-info mb-2">
                                            <i class="fas fa-clock me-2"></i>
                                            Clocked in: {{ $drive->formatForUser($timeLog->clock_in->copy()->setTimezone('UTC'), 'M j, g:i A', auth()->user()) }}
                                            @if($hasClockedOut)
                                                <br><i class="fas fa-sign-out-alt me-2"></i>
                                                Clocked out: {{ $drive->formatForUser($timeLog->clock_out->copy()->setTimezone('UTC'), 'M j, g:i A', auth()->user()) }}
                                                <br><strong>Total hours: {{ number_format($timeLog->total_hours, 2) }}</strong>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <div class="ms-3">
                            @if(in_array($schedule->status, ['scheduled', 'confirmed']))
                                @if(!$hasClockedIn)
                                    <form action="{{ route('user-self-service.clock-in', [$drive, $schedule]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="fas fa-sign-in-alt me-2"></i>Clock In
                                        </button>
                                    </form>
                                    <div class="mt-2">
                                        <a href="{{ route('user-self-service.create-time-log-for-schedule', [$drive, $schedule]) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-edit me-2"></i>Enter Hours Manually
                                        </a>
                                    </div>
                                @elseif(!$hasClockedOut)
                                    <form action="{{ route('user-self-service.clock-out', [$drive, $schedule]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-lg">
                                            <i class="fas fa-sign-out-alt me-2"></i>Clock Out
                                        </button>
                                    </form>
                                    @if($timeLog)
                                        <a href="{{ route('user-self-service.edit-time-log', [$drive, $timeLog]) }}" class="btn btn-outline-primary mt-2 d-block">
                                            <i class="fas fa-edit me-2"></i>Edit Hours
                                        </a>
                                    @endif
                                @else
                                    <span class="badge bg-success mb-2 d-block">Completed</span>
                                    @if($timeLog)
                                        <a href="{{ route('user-self-service.edit-time-log', [$drive, $timeLog]) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-edit me-2"></i>Edit Hours
                                        </a>
                                    @endif
                                @endif
                            @elseif($schedule->status === 'completed' && $timeLog)
                                <span class="badge bg-secondary mb-2 d-block">Completed</span>
                                @if($timeLog->status !== 'approved')
                                    <a href="{{ route('user-self-service.edit-time-log', [$drive, $timeLog]) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit me-2"></i>Edit Hours
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No upcoming schedules found.
            </div>
        @endforelse
    </div>

    <!-- Past Schedules (Last 30 Days) -->
    @if($pastSchedules->count() > 0)
        <div class="dashboard-card">
            <h4 class="mb-3 brand-teal">
                <i class="fas fa-history me-2"></i>Recent Shifts (Last 30 Days)
            </h4>
            
            @foreach($pastSchedules as $schedule)
                @php
                    $scheduleDate = \Carbon\Carbon::parse($schedule->start_date);
                    $timeLog = $drive->timeLogs()
                        ->where('person_id', $person->id)
                        ->where('schedule_id', $schedule->id)
                        ->where('work_date', $scheduleDate->format('Y-m-d'))
                        ->first();
                    
                    $hasClockedIn = $timeLog && $timeLog->clock_in;
                    $hasClockedOut = $timeLog && $timeLog->clock_out;
                @endphp
                
                <div class="card mb-2">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <strong>{{ $schedule->title ?? 'Shift' }}</strong>
                                <span class="text-muted ms-2">{{ $scheduleDate->format('M j, Y') }}</span>
                                <span class="text-muted ms-2">
                                    {{ \Carbon\Carbon::parse($schedule->getStartTimeForUser(auth()->user()))->format('g:i A') }} - 
                                    {{ \Carbon\Carbon::parse($schedule->getEndTimeForUser(auth()->user()))->format('g:i A') }}
                                </span>
                                @if($timeLog && $hasClockedIn)
                                    <span class="ms-2">
                                        @if($hasClockedOut)
                                            <span class="badge bg-info">
                                                {{ $drive->formatForUser($timeLog->clock_in->copy()->setTimezone('UTC'), 'g:i A', auth()->user()) }} - 
                                                {{ $drive->formatForUser($timeLog->clock_out->copy()->setTimezone('UTC'), 'g:i A', auth()->user()) }}
                                            </span>
                                        @else
                                            <span class="badge bg-warning">Clocked In Only</span>
                                        @endif
                                    </span>
                                @endif
                            </div>
                            <div>
                                @if(in_array($schedule->status, ['scheduled', 'confirmed']))
                                    @if(!$hasClockedIn)
                                        <form action="{{ route('user-self-service.clock-in', [$drive, $schedule]) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success me-2">
                                                <i class="fas fa-sign-in-alt me-1"></i>Clock In
                                            </button>
                                        </form>
                                        <a href="{{ route('user-self-service.create-time-log-for-schedule', [$drive, $schedule]) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i>Enter Hours
                                        </a>
                                    @elseif(!$hasClockedOut)
                                        <form action="{{ route('user-self-service.clock-out', [$drive, $schedule]) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger me-2">
                                                <i class="fas fa-sign-out-alt me-1"></i>Clock Out
                                            </button>
                                        </form>
                                        @if($timeLog)
                                            <a href="{{ route('user-self-service.edit-time-log', [$drive, $timeLog]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a>
                                        @endif
                                    @else
                                        @if($timeLog)
                                            <span class="badge bg-success me-2">
                                                {{ number_format($timeLog->total_hours, 2) }} hrs
                                            </span>
                                            @if($timeLog->status !== 'approved')
                                                <a href="{{ route('user-self-service.edit-time-log', [$drive, $timeLog]) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </a>
                                            @endif
                                        @endif
                                    @endif
                                @elseif($schedule->status === 'completed' && $timeLog)
                                    @if($timeLog->clock_in && $timeLog->clock_out)
                                        <span class="badge bg-success me-2">
                                            {{ number_format($timeLog->total_hours, 2) }} hrs
                                        </span>
                                    @elseif($timeLog->clock_in)
                                        <span class="badge bg-warning me-2">Clocked In</span>
                                    @endif
                                    @if($timeLog->status !== 'approved')
                                        <a href="{{ route('user-self-service.edit-time-log', [$drive, $timeLog]) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                    @endif
                                @else
                                    @if($timeLog)
                                        @if($timeLog->clock_in && $timeLog->clock_out)
                                            <span class="badge bg-success me-2">
                                                {{ number_format($timeLog->total_hours, 2) }} hrs
                                            </span>
                                        @elseif($timeLog->clock_in)
                                            <span class="badge bg-warning me-2">Clocked In</span>
                                        @endif
                                        @if($timeLog->status !== 'approved')
                                            <a href="{{ route('user-self-service.edit-time-log', [$drive, $timeLog]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">No time logged</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

