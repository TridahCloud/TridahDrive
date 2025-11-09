@extends('layouts.dashboard')

@section('title', 'Create People Manager Profile - ' . $drive->name)

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
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">Create People Manager Profile</h1>
                    <p class="text-muted">{{ $drive->name }}</p>
                </div>
                <a href="{{ route('drives.people-manager.dashboard', $drive) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('drives.people-manager.profiles.store', $drive) }}" method="POST">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Profile Information</h4>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Profile Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g., Main Office, Remote Team, Volunteers">
                        <small class="text-muted">A descriptive name for this profile</small>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_default">Set as default profile</label>
                    </div>
                </div>

                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Organization Information</h4>
                    
                    <div class="mb-3">
                        <label for="organization_name" class="form-label">Organization Name</label>
                        <input type="text" class="form-control @error('organization_name') is-invalid @enderror" id="organization_name" name="organization_name" value="{{ old('organization_name') }}" placeholder="Your Organization Name">
                        @error('organization_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="organization_address" class="form-label">Address</label>
                        <textarea class="form-control @error('organization_address') is-invalid @enderror" id="organization_address" name="organization_address" rows="2" placeholder="Street, City, State, ZIP">{{ old('organization_address') }}</textarea>
                        @error('organization_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="organization_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control @error('organization_phone') is-invalid @enderror" id="organization_phone" name="organization_phone" value="{{ old('organization_phone') }}" placeholder="+1 (555) 123-4567">
                            @error('organization_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="organization_email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('organization_email') is-invalid @enderror" id="organization_email" name="organization_email" value="{{ old('organization_email') }}" placeholder="info@organization.com">
                            @error('organization_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Payroll Defaults</h4>
                    
                    <div class="mb-3">
                        <label for="default_pay_frequency" class="form-label">Default Pay Frequency</label>
                        <select class="form-select @error('default_pay_frequency') is-invalid @enderror" id="default_pay_frequency" name="default_pay_frequency">
                            <option value="biweekly" {{ old('default_pay_frequency', 'biweekly') === 'biweekly' ? 'selected' : '' }}>Biweekly</option>
                            <option value="weekly" {{ old('default_pay_frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('default_pay_frequency') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="custom" {{ old('default_pay_frequency') === 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                        <small class="text-muted">Default pay frequency for new employees</small>
                        @error('default_pay_frequency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="default_overtime_threshold" class="form-label">Overtime Threshold (hours/week)</label>
                            <input type="number" class="form-control @error('default_overtime_threshold') is-invalid @enderror" id="default_overtime_threshold" name="default_overtime_threshold" value="{{ old('default_overtime_threshold', 40) }}" step="0.01" min="0" max="168" placeholder="40.00">
                            <small class="text-muted">Hours per week before overtime applies</small>
                            @error('default_overtime_threshold')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="default_overtime_multiplier" class="form-label">Overtime Multiplier</label>
                            <input type="number" class="form-control @error('default_overtime_multiplier') is-invalid @enderror" id="default_overtime_multiplier" name="default_overtime_multiplier" value="{{ old('default_overtime_multiplier', 1.5) }}" step="0.01" min="1" max="3" placeholder="1.5">
                            <small class="text-muted">Overtime rate multiplier (e.g., 1.5 for time and a half)</small>
                            @error('default_overtime_multiplier')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Display Settings</h4>
                    
                    <div class="mb-3">
                        <label for="accent_color" class="form-label">Accent Color</label>
                        <input type="color" class="form-control form-control-color" id="accent_color" name="accent_color" value="{{ old('accent_color', '#31d8b2') }}" title="Choose accent color">
                        <small class="text-muted">Color used throughout the People Manager interface</small>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('drives.people-manager.dashboard', $drive) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Profile
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection




