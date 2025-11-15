@extends('layouts.dashboard')

@section('title', 'Time Log - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('drives.people-manager.time-logs.index', $drive) }}">Time Logs</a></li>
                            <li class="breadcrumb-item active">View</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">Time Log</h1>
                </div>
                <a href="{{ route('drives.people-manager.time-logs.index', $drive) }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <h4>{{ $timeLog->person->full_name ?? 'Unknown' }}</h4>
        <p><strong>Date:</strong> {{ $timeLog->work_date->format('M d, Y') }}</p>
        @if($timeLog->total_hours)
            <p><strong>Hours:</strong> {{ number_format($timeLog->total_hours, 1) }}</p>
        @endif
        @if($timeLog->work_description)
            <div class="mb-3">
                <strong>Description of Work:</strong>
                <div class="mt-2 p-3 bg-light rounded">
                    {{ nl2br(e($timeLog->work_description)) }}
                </div>
            </div>
        @endif
        <p><strong>Status:</strong> 
            <span class="badge bg-{{ $timeLog->status === 'approved' ? 'success' : ($timeLog->status === 'pending' ? 'warning' : 'secondary') }}">
                {{ ucfirst($timeLog->status) }}
            </span>
        </p>
    </div>
</div>
@endsection

