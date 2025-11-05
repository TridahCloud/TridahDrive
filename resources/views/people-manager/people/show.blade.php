@extends('layouts.dashboard')

@section('title', $person->full_name . ' - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('drives.people-manager.people.index', $drive) }}">People</a></li>
                            <li class="breadcrumb-item active">{{ $person->full_name }}</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">{{ $person->full_name }}</h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div>
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.people-manager.people.edit', [$drive, $person]) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit
                        </a>
                    @endif
                    <a href="{{ route('drives.people-manager.people.index', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="dashboard-card mb-4">
                <h4 class="mb-3 brand-teal">Basic Information</h4>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Name</label>
                        <p class="mb-0"><strong>{{ $person->full_name }}</strong></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Type</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $person->type === 'employee' ? 'primary' : ($person->type === 'contractor' ? 'info' : 'success') }}">
                                {{ ucfirst($person->type) }}
                            </span>
                        </p>
                    </div>
                </div>

                @if($person->email)
                    <div class="mb-3">
                        <label class="text-muted small">Email</label>
                        <p class="mb-0"><a href="mailto:{{ $person->email }}">{{ $person->email }}</a></p>
                    </div>
                @endif

                @if($person->phone)
                    <div class="mb-3">
                        <label class="text-muted small">Phone</label>
                        <p class="mb-0"><a href="tel:{{ $person->phone }}">{{ $person->phone }}</a></p>
                    </div>
                @endif

                @if($person->job_title)
                    <div class="mb-3">
                        <label class="text-muted small">Job Title</label>
                        <p class="mb-0">{{ $person->job_title }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

