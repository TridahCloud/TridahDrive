@extends('layouts.dashboard')

@section('title', 'People Manager Profiles - ' . $drive->name)

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
                            <li class="breadcrumb-item active">Profiles</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        <i class="fas fa-users me-2"></i>People Manager Profiles
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div>
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.people-manager.profiles.create', $drive) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>New Profile
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

    @forelse($profiles as $profile)
        <div class="dashboard-card mb-3">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h5 class="mb-0">{{ $profile->name }}</h5>
                        @if($profile->is_default)
                            <span class="badge bg-brand-teal">Default</span>
                        @endif
                    </div>
                    @if($profile->organization_name)
                        <p class="mb-2"><strong>{{ $profile->organization_name }}</strong></p>
                    @endif
                    @if($profile->organization_address)
                        <p class="text-muted mb-1 small">{{ $profile->organization_address }}</p>
                    @endif
                    @if($profile->organization_email || $profile->organization_phone)
                        <p class="text-muted mb-1 small">
                            @if($profile->organization_email){{ $profile->organization_email }}@endif
                            @if($profile->organization_email && $profile->organization_phone) &bull; @endif
                            @if($profile->organization_phone){{ $profile->organization_phone }}@endif
                        </p>
                    @endif
                    <p class="text-muted small mb-0">
                        Pay Frequency: <strong>{{ ucfirst($profile->default_pay_frequency) }}</strong> &bull; 
                        Overtime Threshold: <strong>{{ $profile->default_overtime_threshold }} hours</strong> &bull;
                        Overtime Rate: <strong>{{ $profile->default_overtime_multiplier }}x</strong>
                    </p>
                </div>
                @if($drive->canEdit(auth()->user()))
                    <div class="d-flex gap-2">
                        <a href="{{ route('drives.people-manager.profiles.edit', [$drive, $profile]) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('drives.people-manager.profiles.destroy', [$drive, $profile]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this profile?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="dashboard-card text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h5>No People Manager Profiles Yet</h5>
            <p class="text-muted">Create your first profile to set up organization information and payroll defaults</p>
            @if($drive->canEdit(auth()->user()))
                <a href="{{ route('drives.people-manager.profiles.create', $drive) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create Profile
                </a>
            @endif
        </div>
    @endforelse
</div>
@endsection

