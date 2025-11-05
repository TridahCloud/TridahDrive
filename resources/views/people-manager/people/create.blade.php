@extends('layouts.dashboard')

@section('title', 'Create Person - ' . $drive->name)

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
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 mb-0 brand-teal">Add Person</h1>
                    <p class="text-muted">{{ $drive->name }}</p>
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

    <form action="{{ route('drives.people-manager.people.store', $drive) }}" method="POST">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Link to Drive User (Optional)</h4>
                    <p class="text-muted small mb-3">Link this person to an existing Drive user to enable self-service features like clocking hours and viewing schedules.</p>
                    
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Select Drive User</label>
                        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                            <option value="">Create without linking to a user</option>
                            @foreach($driveUsers as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($driveUsers->isEmpty())
                            <div class="form-text text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                No Drive users found. 
                                @if($drive->owner_id)
                                    Owner ID: {{ $drive->owner_id }}. 
                                @endif
                                Make sure users are added to this Drive.
                            </div>
                        @else
                            <div class="form-text text-muted">Selecting a user will auto-populate name and email fields.</div>
                        @endif
                    </div>
                </div>

                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Basic Information</h4>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Type *</label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="employee" {{ old('type') === 'employee' ? 'selected' : '' }}>Employee</option>
                            <option value="contractor" {{ old('type') === 'contractor' ? 'selected' : '' }}>Contractor</option>
                            <option value="volunteer" {{ old('type') === 'volunteer' ? 'selected' : '' }}>Volunteer</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status *</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="terminated" {{ old('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="dashboard-card mb-4">
                    <h4 class="mb-3 brand-teal">Employment Details</h4>
                    
                    <div class="mb-3">
                        <label for="job_title" class="form-label">Job Title</label>
                        <input type="text" class="form-control" id="job_title" name="job_title" value="{{ old('job_title') }}">
                    </div>

                    <div class="mb-3">
                        <label for="people_manager_profile_id" class="form-label">Profile</label>
                        <select class="form-select" id="people_manager_profile_id" name="people_manager_profile_id">
                            <option value="">None</option>
                            @foreach($profiles as $profile)
                                <option value="{{ $profile->id }}" {{ old('people_manager_profile_id') == $profile->id ? 'selected' : '' }}>
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
                            <option value="hourly" {{ old('pay_type') === 'hourly' ? 'selected' : '' }}>Hourly</option>
                            <option value="salary" {{ old('pay_type') === 'salary' ? 'selected' : '' }}>Salary</option>
                            <option value="contract" {{ old('pay_type') === 'contract' ? 'selected' : '' }}>Contract</option>
                            <option value="volunteer" {{ old('pay_type') === 'volunteer' ? 'selected' : '' }}>Volunteer</option>
                        </select>
                        @error('pay_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="hourly_pay_fields" style="display: {{ old('pay_type') === 'hourly' ? 'block' : 'none' }};">
                        <div class="mb-3">
                            <label for="hourly_rate" class="form-label">Hourly Rate</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control @error('hourly_rate') is-invalid @enderror" 
                                       id="hourly_rate" name="hourly_rate" value="{{ old('hourly_rate') }}" 
                                       placeholder="0.00">
                                <span class="input-group-text">per hour</span>
                            </div>
                            @error('hourly_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div id="salary_pay_fields" style="display: {{ old('pay_type') === 'salary' ? 'block' : 'none' }};">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="salary_amount" class="form-label">Salary Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control @error('salary_amount') is-invalid @enderror" 
                                           id="salary_amount" name="salary_amount" value="{{ old('salary_amount') }}" 
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
                                    <option value="weekly" {{ old('salary_frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="biweekly" {{ old('salary_frequency') === 'biweekly' ? 'selected' : '' }}>Bi-weekly</option>
                                    <option value="monthly" {{ old('salary_frequency') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="annually" {{ old('salary_frequency') === 'annually' ? 'selected' : '' }}>Annually</option>
                                </select>
                                @error('salary_frequency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle me-1"></i>
                            Salary will be converted to hourly rate for time log calculations (based on 2080 hours/year)
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('drives.people-manager.people.index', $drive) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Person
                    </button>
                </div>
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
    
    // Auto-populate name and email when user is selected
    const userIdSelect = document.getElementById('user_id');
    const firstNameInput = document.getElementById('first_name');
    const emailInput = document.getElementById('email');
    
    userIdSelect.addEventListener('change', function() {
        if (this.value && !firstNameInput.value) {
            const selectedOption = this.options[this.selectedIndex];
            const text = selectedOption.text;
            // Extract name from format "Name (email)"
            const nameMatch = text.match(/^(.+?)\s*\(/);
            if (nameMatch) {
                const fullName = nameMatch[1].trim();
                const nameParts = fullName.split(' ');
                if (nameParts.length > 0) {
                    firstNameInput.value = nameParts[0];
                    if (nameParts.length > 1) {
                        document.getElementById('last_name').value = nameParts.slice(1).join(' ');
                    }
                }
            }
        }
        
        if (this.value && !emailInput.value) {
            const selectedOption = this.options[this.selectedIndex];
            const text = selectedOption.text;
            // Extract email from format "Name (email)"
            const emailMatch = text.match(/\(([^)]+)\)/);
            if (emailMatch) {
                emailInput.value = emailMatch[1];
            }
        }
    });
});
</script>
@endsection

