@extends('layouts.dashboard')

@section('title', $profile->name . ' - People Manager Profile - ' . $drive->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('drives.people-manager.profiles.index', $drive) }}">Profiles</a></li>
                            <li class="breadcrumb-item active">{{ $profile->name }}</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">
                        {{ $profile->name }}
                        @if($profile->is_default)
                            <span class="badge bg-brand-teal ms-2">Default</span>
                        @endif
                    </h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <div>
                    @if($drive->canEdit(auth()->user()))
                        <a href="{{ route('drives.people-manager.profiles.edit', [$drive, $profile]) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit
                        </a>
                    @endif
                    <a href="{{ route('drives.people-manager.profiles.index', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            @if($profile->organization_name || $profile->organization_address || $profile->organization_email || $profile->organization_phone)
                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Organization Information</h4>
                    
                    @if($profile->organization_name)
                        <p class="mb-2"><strong>{{ $profile->organization_name }}</strong></p>
                    @endif
                    
                    @if($profile->organization_address)
                        <p class="text-muted mb-2">{{ $profile->organization_address }}</p>
                    @endif
                    
                    @if($profile->organization_email || $profile->organization_phone)
                        <p class="mb-2">
                            @if($profile->organization_email)
                                <i class="fas fa-envelope me-2"></i><a href="mailto:{{ $profile->organization_email }}">{{ $profile->organization_email }}</a>
                            @endif
                            @if($profile->organization_email && $profile->organization_phone)
                                <span class="mx-2">•</span>
                            @endif
                            @if($profile->organization_phone)
                                <i class="fas fa-phone me-2"></i><a href="tel:{{ $profile->organization_phone }}">{{ $profile->organization_phone }}</a>
                            @endif
                        </p>
                    @endif
                </div>
            @endif

            <div class="dashboard-card mb-4">
                <h4 class="mb-3 brand-teal">Payroll Defaults</h4>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Pay Frequency</label>
                        <p class="mb-0"><strong>{{ ucfirst($profile->default_pay_frequency) }}</strong></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Overtime Threshold</label>
                        <p class="mb-0"><strong>{{ number_format($profile->default_overtime_threshold, 2) }} hours/week</strong></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Overtime Multiplier</label>
                        <p class="mb-0"><strong>{{ $profile->default_overtime_multiplier }}x</strong></p>
                    </div>
                </div>
            </div>

            <div class="dashboard-card mb-4">
                <h4 class="mb-3 brand-teal">Display Settings</h4>
                
                <div class="d-flex align-items-center gap-3">
                    <label class="text-muted small mb-0">Accent Color:</label>
                    <div style="width: 40px; height: 40px; background-color: {{ $profile->accent_color }}; border-radius: 4px; border: 1px solid #ddd;"></div>
                    <span class="text-muted">{{ $profile->accent_color }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

