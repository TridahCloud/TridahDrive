@extends('layouts.dashboard')

@section('title', 'Edit Schedule - ' . $drive->name)

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
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">Edit Schedule</h1>
                </div>
                <a href="{{ route('drives.people-manager.schedules.index', $drive) }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
    </div>

    <form action="{{ route('drives.people-manager.schedules.update', [$drive, $schedule]) }}" method="POST">
        @csrf
        @method('PATCH')
        <div class="dashboard-card mb-4">
            <div class="mb-3">
                <label for="title" class="form-label">Title *</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $schedule->title) }}" required>
            </div>
            <div class="mb-3">
                <label for="person_id" class="form-label">Person</label>
                <select class="form-select" id="person_id" name="person_id">
                    <option value="">None</option>
                    @foreach($people as $person)
                        <option value="{{ $person->id }}" {{ old('person_id', $schedule->person_id) == $person->id ? 'selected' : '' }}>
                            {{ $person->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="start_date" class="form-label">Start Date *</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', $schedule->start_date?->format('Y-m-d')) }}" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="start_time" class="form-label">Start Time *</label>
                    <input type="time" class="form-control" id="start_time" name="start_time" value="{{ old('start_time', $schedule->start_time) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="end_time" class="form-label">End Time *</label>
                    <input type="time" class="form-control" id="end_time" name="end_time" value="{{ old('end_time', $schedule->end_time) }}" required>
                </div>
            </div>
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('drives.people-manager.schedules.index', $drive) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Schedule</button>
            </div>
        </div>
    </form>
</div>
@endsection

