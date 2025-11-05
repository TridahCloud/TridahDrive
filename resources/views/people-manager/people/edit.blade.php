@extends('layouts.dashboard')

@section('title', 'Edit Person - ' . $drive->name)

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
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">Edit Person</h1>
                    <p class="text-muted">{{ $person->full_name }} - {{ $drive->name }}</p>
                </div>
                <a href="{{ route('drives.people-manager.people.index', $drive) }}" class="btn btn-outline-secondary">
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

    <form action="{{ route('drives.people-manager.people.update', [$drive, $person]) }}" method="POST">
        @csrf
        @method('PATCH')
        
        <div class="row">
            <div class="col-lg-8">
                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Link to Drive User (Optional)</h4>
                    <p class="text-muted small mb-3">Link this person to an existing Drive user to enable self-service features like clocking hours and viewing schedules.</p>
                    
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Select Drive User</label>
                        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                            <option value="">Not linked to a user</option>
                            @foreach($driveUsers as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $person->user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($person->user_id)
                            <div class="form-text text-success">
                                <i class="fas fa-check-circle me-1"></i>Currently linked to: {{ $person->user->name ?? 'Unknown' }}
                            </div>
                        @endif
                        @if($driveUsers->isEmpty())
                            <div class="form-text text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                No Drive users found. 
                                @if($drive->owner_id)
                                    Owner ID: {{ $drive->owner_id }}. 
                                @endif
                                Make sure users are added to this Drive.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Basic Information</h4>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $person->first_name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $person->last_name) }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $person->email) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $person->phone) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Type *</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="employee" {{ old('type', $person->type) === 'employee' ? 'selected' : '' }}>Employee</option>
                            <option value="contractor" {{ old('type', $person->type) === 'contractor' ? 'selected' : '' }}>Contractor</option>
                            <option value="volunteer" {{ old('type', $person->type) === 'volunteer' ? 'selected' : '' }}>Volunteer</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status *</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active" {{ old('status', $person->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $person->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="terminated" {{ old('status', $person->status) === 'terminated' ? 'selected' : '' }}>Terminated</option>
                        </select>
                    </div>
                </div>

                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Employment Details</h4>
                    
                    <div class="mb-3">
                        <label for="job_title" class="form-label">Job Title</label>
                        <input type="text" class="form-control" id="job_title" name="job_title" value="{{ old('job_title', $person->job_title) }}">
                    </div>

                    <div class="mb-3">
                        <label for="people_manager_profile_id" class="form-label">Profile</label>
                        <select class="form-select" id="people_manager_profile_id" name="people_manager_profile_id">
                            <option value="">None</option>
                            @foreach($profiles as $profile)
                                <option value="{{ $profile->id }}" {{ old('people_manager_profile_id', $person->people_manager_profile_id) == $profile->id ? 'selected' : '' }}>
                                    {{ $profile->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Pay Rate</h4>
                    <p class="text-muted small mb-3">Set the pay rate to automatically calculate pay in time logs</p>
                    
                    <div class="mb-3">
                        <label for="pay_type" class="form-label">Pay Type</label>
                        <select class="form-select @error('pay_type') is-invalid @enderror" id="pay_type" name="pay_type">
                            <option value="">Not Set</option>
                            <option value="hourly" {{ old('pay_type', $person->pay_type) === 'hourly' ? 'selected' : '' }}>Hourly</option>
                            <option value="salary" {{ old('pay_type', $person->pay_type) === 'salary' ? 'selected' : '' }}>Salary</option>
                            <option value="contract" {{ old('pay_type', $person->pay_type) === 'contract' ? 'selected' : '' }}>Contract</option>
                            <option value="volunteer" {{ old('pay_type', $person->pay_type) === 'volunteer' ? 'selected' : '' }}>Volunteer</option>
                        </select>
                        @error('pay_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="hourly_pay_fields" style="display: {{ old('pay_type', $person->pay_type) === 'hourly' ? 'block' : 'none' }};">
                        <div class="mb-3">
                            <label for="hourly_rate" class="form-label">Hourly Rate</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control @error('hourly_rate') is-invalid @enderror" 
                                       id="hourly_rate" name="hourly_rate" value="{{ old('hourly_rate', $person->hourly_rate) }}" 
                                       placeholder="0.00">
                                <span class="input-group-text">per hour</span>
                            </div>
                            @error('hourly_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div id="salary_pay_fields" style="display: {{ old('pay_type', $person->pay_type) === 'salary' ? 'block' : 'none' }};">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="salary_amount" class="form-label">Salary Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control @error('salary_amount') is-invalid @enderror" 
                                           id="salary_amount" name="salary_amount" value="{{ old('salary_amount', $person->salary_amount) }}" 
                                           placeholder="0.00">
                                </div>
                                @error('salary_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="salary_frequency" class="form-label">Frequency</label>
                                <select class="form-select @error('salary_frequency') is-invalid @enderror" 
                                        id="salary_frequency" name="salary_frequency">
                                    <option value="">Select frequency</option>
                                    <option value="weekly" {{ old('salary_frequency', $person->salary_frequency) === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="biweekly" {{ old('salary_frequency', $person->salary_frequency) === 'biweekly' ? 'selected' : '' }}>Bi-weekly</option>
                                    <option value="monthly" {{ old('salary_frequency', $person->salary_frequency) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="annually" {{ old('salary_frequency', $person->salary_frequency) === 'annually' ? 'selected' : '' }}>Annually</option>
                                </select>
                                @error('salary_frequency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle me-1"></i>
                            Salary will be converted to hourly rate for time log calculations (based on 2080 hours/year)
                            @if($person->pay_type === 'salary' && $person->salary_amount && $person->salary_frequency)
                                <br><strong>Current hourly equivalent: ${{ number_format($person->getEffectiveHourlyRate(), 2) }}/hour</strong>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="dashboard-card mb-4">
                <h5 class="mb-3">Quick Actions</h5>
                <a href="{{ route('drives.people-manager.people.index', $drive) }}" class="btn btn-secondary w-100 mb-2">Cancel</a>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save me-2"></i>Update Person
                </button>
            </div>
            
            <div class="dashboard-card">
                <h6 class="mb-2">Role Assignment</h6>
                <p class="text-muted small mb-2">
                    Roles are assigned at the Drive level. 
                    <a href="{{ route('drives.edit', $drive) }}">Go to Drive Settings</a> to assign roles to this person.
                </p>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const payTypeSelect = document.getElementById('pay_type');
    const hourlyFields = document.getElementById('hourly_pay_fields');
    const salaryFields = document.getElementById('salary_pay_fields');
    
    payTypeSelect.addEventListener('change', function() {
        if (this.value === 'hourly') {
            hourlyFields.style.display = 'block';
            salaryFields.style.display = 'none';
        } else if (this.value === 'salary') {
            hourlyFields.style.display = 'none';
            salaryFields.style.display = 'block';
        } else {
            hourlyFields.style.display = 'none';
            salaryFields.style.display = 'none';
        }
    });
});
</script>
@endsection

