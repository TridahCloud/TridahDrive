@extends('layouts.dashboard')

@section('title', 'Schedules - ' . $drive->name)

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
                            <li class="breadcrumb-item active">Schedules</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal"><i class="fas fa-calendar-alt me-2"></i>Schedules</h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div>
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.people-manager.schedules.builder', $drive) }}" class="btn btn-success me-2">
                            <i class="fas fa-calendar-alt me-2"></i>Schedule Builder
                        </a>
                        <a href="{{ route('drives.people-manager.schedules.create', $drive) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>New Schedule
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

    @forelse($schedules as $schedule)
        <div class="dashboard-card mb-3">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h5 class="mb-2">{{ $schedule->title }}</h5>
                    @if($schedule->person)
                        <p class="text-muted mb-1 small"><i class="fas fa-user me-1"></i>{{ $schedule->person->full_name }}</p>
                    @endif
                    <p class="text-muted mb-0 small">
                        <i class="fas fa-calendar me-1"></i>{{ $schedule->start_date->format('M d, Y') }}
                        @if($schedule->start_time)
                            <i class="fas fa-clock ms-2 me-1"></i>{{ date('g:i A', strtotime($schedule->start_time)) }}
                            @if($schedule->end_time)
                                - {{ date('g:i A', strtotime($schedule->end_time)) }}
                            @endif
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2">
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.people-manager.schedules.edit', [$drive, $schedule]) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('drives.people-manager.schedules.destroy', [$drive, $schedule]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="dashboard-card text-center py-5">
            <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
            <h5>No Schedules Yet</h5>
            <p class="text-muted">Create schedules to manage work shifts and appointments</p>
            @if($drive->canEdit(auth()->user()))
                <a href="{{ route('drives.people-manager.schedules.create', $drive) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create Schedule
                </a>
            @endif
        </div>
    @endforelse
</div>
@endsection

