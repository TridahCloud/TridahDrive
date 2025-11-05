@extends('layouts.dashboard')

@section('title', 'People - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('drives.people-manager.dashboard', $drive) }}">People Manager</a></li>
                            <li class="breadcrumb-item active">People</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-users me-2"></i>People
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div>
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.people-manager.people.create', $drive) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add Person
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

    @forelse($people as $person)
        <div class="dashboard-card mb-3">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h5 class="mb-0">{{ $person->full_name }}</h5>
                        <span class="badge bg-{{ $person->type === 'employee' ? 'primary' : ($person->type === 'contractor' ? 'info' : 'success') }}">
                            {{ ucfirst($person->type) }}
                        </span>
                        @if($person->status === 'active')
                            <span class="badge bg-success">Active</span>
                        @elseif($person->status === 'terminated')
                            <span class="badge bg-danger">Terminated</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                        @if($person->user_id)
                            <span class="badge bg-info" title="Linked to Drive user: {{ $person->user->name ?? 'Unknown' }}">
                                <i class="fas fa-link me-1"></i>Linked User
                            </span>
                        @endif
                    </div>
                    @if($person->job_title)
                        <p class="text-muted mb-1 small">{{ $person->job_title }}</p>
                    @endif
                    @if($person->email)
                        <p class="text-muted mb-1 small">
                            <i class="fas fa-envelope me-1"></i>{{ $person->email }}
                        </p>
                    @endif
                    @if($person->phone)
                        <p class="text-muted mb-0 small">
                            <i class="fas fa-phone me-1"></i>{{ $person->phone }}
                        </p>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('drives.people-manager.people.show', [$drive, $person]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i>
                    </a>
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.people-manager.people.edit', [$drive, $person]) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('drives.people-manager.people.destroy', [$drive, $person]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this person?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="dashboard-card text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h5>No People Yet</h5>
            <p class="text-muted">Add employees, contractors, or volunteers to get started</p>
            @if($drive->canEdit(auth()->user()))
                <a href="{{ route('drives.people-manager.people.create', $drive) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Person
                </a>
            @endif
        </div>
    @endforelse
</div>
@endsection

