@extends('layouts.dashboard')

@section('title', 'Edit Time Log - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('user-self-service.time-logs', $drive) }}">My Time Logs</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-edit me-2"></i>Edit Time Log
                    </h1>
                    <p class="text-muted">{{ $person->full_name }} - {{ $drive->name }}</p>
                </div>
                <div>
                    <a href="{{ route('user-self-service.time-logs', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="dashboard-card">
        <form action="{{ route('user-self-service.update-time-log', [$drive, $timeLog]) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label for="work_date" class="form-label">Work Date</label>
                    <input type="date" class="form-control" id="work_date" value="{{ $timeLog->work_date->format('Y-m-d') }}" disabled>
                    <small class="text-muted">Date cannot be changed</small>
                </div>
                
                @if($timeLog->schedule)
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Scheduled Shift</label>
                        <input type="text" class="form-control" value="{{ $timeLog->schedule->title ?? 'Shift' }} - {{ \Carbon\Carbon::parse($timeLog->schedule->getStartTimeForUser(auth()->user()))->format('g:i A') }} to {{ \Carbon\Carbon::parse($timeLog->schedule->getEndTimeForUser(auth()->user()))->format('g:i A') }}" disabled>
                        <small class="text-muted">Scheduled hours: {{ number_format($timeLog->schedule->total_hours ?? 0, 2) }}</small>
                    </div>
                @endif
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label for="clock_in" class="form-label">Clock In Time</label>
                    @php
                        $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
                        $clockInLocal = $timeLog->clock_in ? $drive->formatForUser($timeLog->clock_in->copy()->setTimezone('UTC'), 'Y-m-d\TH:i', auth()->user()) : '';
                    @endphp
                    <input type="datetime-local" 
                           class="form-control @error('clock_in') is-invalid @enderror" 
                           id="clock_in" 
                           name="clock_in" 
                           value="{{ old('clock_in', $clockInLocal) }}">
                    @error('clock_in')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        Leave blank if you want to manually enter hours instead
                        <br>Times displayed in your timezone: {{ $userTimezone }}
                    </small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="clock_out" class="form-label">Clock Out Time</label>
                    @php
                        $clockOutLocal = $timeLog->clock_out ? $drive->formatForUser($timeLog->clock_out->copy()->setTimezone('UTC'), 'Y-m-d\TH:i', auth()->user()) : '';
                    @endphp
                    <input type="datetime-local" 
                           class="form-control @error('clock_out') is-invalid @enderror" 
                           id="clock_out" 
                           name="clock_out" 
                           value="{{ old('clock_out', $clockOutLocal) }}">
                    @error('clock_out')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        Leave blank if you want to manually enter hours instead
                        <br>Times displayed in your timezone: {{ $userTimezone }}
                    </small>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label for="break_hours" class="form-label">Break Hours</label>
                    <input type="number" 
                           step="0.01" 
                           min="0" 
                           class="form-control @error('break_hours') is-invalid @enderror" 
                           id="break_hours" 
                           name="break_hours" 
                           value="{{ old('break_hours', $timeLog->break_hours ?? 0) }}">
                    @error('break_hours')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="total_hours" class="form-label">Total Hours (Manual Entry)</label>
                    <input type="number" 
                           step="0.01" 
                           min="0" 
                           class="form-control @error('total_hours') is-invalid @enderror" 
                           id="total_hours" 
                           name="total_hours" 
                           value="{{ old('total_hours', $timeLog->total_hours ?? 0) }}">
                    @error('total_hours')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Leave blank if using clock in/out times</small>
                </div>
            </div>

            <div class="mb-4">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" 
                          id="notes" 
                          name="notes" 
                          rows="3" 
                          maxlength="1000">{{ old('notes', $timeLog->notes ?? '') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if($timeLog->clock_in && $timeLog->clock_out)
                <div class="alert alert-info mb-4">
                    <strong>Current Hours:</strong> {{ number_format($timeLog->total_hours ?? 0, 2) }} hours
                    @if($timeLog->regular_hours > 0 || $timeLog->overtime_hours > 0)
                        <br><strong>Regular:</strong> {{ number_format($timeLog->regular_hours ?? 0, 2) }} hours
                        <br><strong>Overtime:</strong> {{ number_format($timeLog->overtime_hours ?? 0, 2) }} hours
                    @endif
                    @if($timeLog->total_pay > 0)
                        <br><strong>Pay:</strong> {{ currency_for($timeLog->total_pay, $drive) }}
                    @endif
                </div>
            @endif

            <div class="d-flex justify-content-between">
                <a href="{{ route('user-self-service.time-logs', $drive) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

