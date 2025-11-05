@extends('layouts.dashboard')

@section('title', 'Schedule - ' . $drive->name)

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
                            <li class="breadcrumb-item active">{{ $schedule->title }}</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">{{ $schedule->title }}</h1>
                </div>
                <div>
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.people-manager.schedules.edit', [$drive, $schedule]) }}" class="btn btn-primary">Edit</a>
                    @endif
                    <a href="{{ route('drives.people-manager.schedules.index', $drive) }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <h4>{{ $schedule->title }}</h4>
        @if($schedule->person)
            <p><strong>Person:</strong> {{ $schedule->person->full_name }}</p>
        @endif
        <p><strong>Date:</strong> {{ $schedule->start_date->format('M d, Y') }}</p>
        @if($schedule->start_time && $schedule->end_time)
            <p><strong>Time:</strong> {{ date('g:i A', strtotime($schedule->start_time)) }} - {{ date('g:i A', strtotime($schedule->end_time)) }}</p>
        @endif
    </div>
</div>
@endsection

