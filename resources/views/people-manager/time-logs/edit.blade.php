@extends('layouts.dashboard')

@section('title', 'Edit Time Log - ' . $drive->name)

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
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">Edit Time Log</h1>
                </div>
                <a href="{{ route('drives.people-manager.time-logs.index', $drive) }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
    </div>

    <form action="{{ route('drives.people-manager.time-logs.update', [$drive, $timeLog]) }}" method="POST">
        @csrf
        @method('PATCH')
        <div class="dashboard-card mb-4">
            <div class="mb-3">
                <label for="person_id" class="form-label">Person *</label>
                <select class="form-select" id="person_id" name="person_id" required>
                    @foreach($people as $person)
                        <option value="{{ $person->id }}" {{ old('person_id', $timeLog->person_id) == $person->id ? 'selected' : '' }}>
                            {{ $person->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="work_date" class="form-label">Work Date *</label>
                <input type="date" class="form-control" id="work_date" name="work_date" value="{{ old('work_date', $timeLog->work_date?->format('Y-m-d')) }}" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="total_hours" class="form-label">Total Hours</label>
                    <input type="number" step="0.01" class="form-control" id="total_hours" name="total_hours" value="{{ old('total_hours', $timeLog->total_hours) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="pending" {{ old('status', $timeLog->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ old('status', $timeLog->status) === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ old('status', $timeLog->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="work_description" class="form-label">Description of Work</label>
                <textarea class="form-control" id="work_description" name="work_description" rows="4" placeholder="Describe the work performed during this time period...">{{ old('work_description', $timeLog->work_description) }}</textarea>
                <small class="form-text text-muted">This description will appear in printed reports.</small>
            </div>
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('drives.people-manager.time-logs.index', $drive) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Time Log</button>
            </div>
        </div>
    </form>
</div>
@endsection

