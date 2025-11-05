@extends('layouts.dashboard')

@section('title', 'Time Logs - ' . $drive->name)

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
                            <li class="breadcrumb-item active">Time Logs</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal"><i class="fas fa-clock me-2"></i>Time Logs</h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div>
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.people-manager.time-logs.create', $drive) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>New Time Log
                        </a>
                    @endif
                    <a href="{{ route('drives.people-manager.dashboard', $drive) }}" class="btn btn-outline-secondary">
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
        <form method="GET" action="{{ route('drives.people-manager.time-logs.index', $drive) }}" class="row g-3">
            <div class="col-md-3">
                <label for="person_type" class="form-label">Person Type</label>
                <select name="person_type" id="person_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="employee" {{ request('person_type') === 'employee' ? 'selected' : '' }}>Employee</option>
                    <option value="contractor" {{ request('person_type') === 'contractor' ? 'selected' : '' }}>Contractor</option>
                    <option value="volunteer" {{ request('person_type') === 'volunteer' ? 'selected' : '' }}>Volunteer</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="person_id" class="form-label">Person</label>
                <select name="person_id" id="person_id" class="form-select">
                    <option value="">All People</option>
                    @foreach($people as $person)
                        <option value="{{ $person->id }}" {{ request('person_id') == $person->id ? 'selected' : '' }}>
                            {{ $person->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
                <a href="{{ route('drives.people-manager.time-logs.index', $drive) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Clear
                </a>
            </div>
        </form>
    </div>

    @forelse($timeLogs as $timeLog)
        <div class="dashboard-card mb-3">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h5 class="mb-0">{{ $timeLog->person->full_name ?? 'Unknown' }}</h5>
                        <span class="badge bg-info">
                            {{ ucfirst($timeLog->person->type ?? 'Unknown') }}
                        </span>
                    </div>
                    <p class="text-muted mb-1 small">
                        <i class="fas fa-calendar me-1"></i>{{ $drive->formatForUser(\Carbon\Carbon::parse($timeLog->work_date), 'M d, Y', auth()->user()) }}
                        @if($timeLog->total_hours)
                            <i class="fas fa-clock ms-2 me-1"></i>{{ number_format($timeLog->total_hours, 1) }} hours
                        @endif
                        @if($timeLog->total_pay)
                            <span class="ms-2 text-success">
                                <i class="fas fa-dollar-sign me-1"></i>{{ currency_for($timeLog->total_pay, $drive) }}
                            </span>
                        @endif
                    </p>
                    <span class="badge bg-{{ $timeLog->status === 'approved' ? 'success' : ($timeLog->status === 'pending' ? 'warning' : 'secondary') }}">
                        {{ ucfirst($timeLog->status) }}
                    </span>
                </div>
                <div class="d-flex gap-2">
                    @if($drive->canEdit(auth()->user()) && $timeLog->status === 'pending')
                        <form action="{{ route('drives.people-manager.time-logs.approve', [$drive, $timeLog]) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="fas fa-check"></i> Approve
                            </button>
                        </form>
                        <form action="{{ route('drives.people-manager.time-logs.reject', [$drive, $timeLog]) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </form>
                    @endif
                    @if($timeLog->person_id)
                        <a href="{{ route('drives.people-manager.time-logs.print-report', [$drive, $timeLog->person]) }}" 
                           class="btn btn-sm btn-info" 
                           target="_blank"
                           title="Print Hours Report">
                            <i class="fas fa-print"></i> Report
                        </a>
                    @endif
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.people-manager.time-logs.edit', [$drive, $timeLog]) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('drives.people-manager.time-logs.destroy', [$drive, $timeLog]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this time log? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Time Log">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="dashboard-card text-center py-5">
            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
            <h5>No Time Logs Found</h5>
            <p class="text-muted">
                @if(request()->has('person_type') || request()->has('person_id'))
                    Try adjusting your filters or
                @else
                    Track hours worked by people
                @endif
            </p>
            @if($drive->canEdit(auth()->user()))
                <a href="{{ route('drives.people-manager.time-logs.create', $drive) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create Time Log
                </a>
            @endif
        </div>
    @endforelse
</div>
@endsection

