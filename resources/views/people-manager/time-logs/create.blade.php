@extends('layouts.dashboard')

@section('title', 'Create Time Log - ' . $drive->name)

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
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">Create Time Log</h1>
                </div>
                <a href="{{ route('drives.people-manager.time-logs.index', $drive) }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
    </div>

    <form action="{{ route('drives.people-manager.time-logs.store', $drive) }}" method="POST">
        @csrf
        <div class="dashboard-card mb-4">
            <div class="mb-3">
                <label for="person_id" class="form-label">Person *</label>
                <select class="form-select" id="person_id" name="person_id" required>
                    @foreach($people as $person)
                        <option value="{{ $person->id }}">{{ $person->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="work_date" class="form-label">Work Date *</label>
                <input type="date" class="form-control" id="work_date" name="work_date" value="{{ old('work_date') }}" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="total_hours" class="form-label">Total Hours</label>
                    <input type="number" step="0.01" class="form-control" id="total_hours" name="total_hours" value="{{ old('total_hours') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ old('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ old('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('drives.people-manager.time-logs.index', $drive) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Time Log</button>
            </div>
        </div>
    </form>
</div>
@endsection

