@extends('layouts.dashboard')

@section('title', 'Edit Drive')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0 brand-teal">Edit Drive</h2>
                    <a href="{{ route('drives.show', $drive) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back
                    </a>
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
                
                <form action="{{ route('drives.update', $drive) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Drive Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $drive->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description', $drive->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="currency" class="form-label">Currency</label>
                        <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency">
                            @foreach(\App\Helpers\CurrencyHelper::getAllCurrencies() as $code => $currency)
                                <option value="{{ $code }}" {{ old('currency', $drive->currency ?? (auth()->user()->currency ?? 'USD')) === $code ? 'selected' : '' }}>
                                    {{ $currency['name'] }} ({{ $currency['symbol'] }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            @if($drive->type === 'shared')
                                Currency used for transactions in this drive
                            @else
                                Currency used for transactions in your personal drive (defaults to your profile currency)
                            @endif
                        </small>
                        @error('currency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="timezone" class="form-label">Timezone</label>
                        <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone">
                            @foreach(\App\Helpers\TimezoneHelper::getCommonTimezones() as $tz => $label)
                                <option value="{{ $tz }}" {{ old('timezone', $drive->timezone ?? 'UTC') === $tz ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            All dates and times in this drive will be stored in this timezone. Users can view times in their own timezone preference.
                        </small>
                        @error('timezone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="color" class="form-label">Color</label>
                            <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror" 
                                   id="color" name="color" value="{{ old('color', $drive->color ?? '#31d8b2') }}">
                            @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="icon" class="form-label">Icon</label>
                            <select class="form-select @error('icon') is-invalid @enderror" id="icon" name="icon">
                                <option value="folder" {{ old('icon', $drive->icon) === 'folder' ? 'selected' : '' }}>Folder</option>
                                <option value="briefcase" {{ old('icon', $drive->icon) === 'briefcase' ? 'selected' : '' }}>Briefcase</option>
                                <option value="building" {{ old('icon', $drive->icon) === 'building' ? 'selected' : '' }}>Building</option>
                                <option value="users" {{ old('icon', $drive->icon) === 'users' ? 'selected' : '' }}>Team</option>
                                <option value="project-diagram" {{ old('icon', $drive->icon) === 'project-diagram' ? 'selected' : '' }}>Project</option>
                                <option value="folder-open" {{ old('icon', $drive->icon) === 'folder-open' ? 'selected' : '' }}>Open Folder</option>
                            </select>
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="mt-3">
                                <small class="text-muted d-block mb-2">Icon Preview:</small>
                                <div class="d-flex gap-2 flex-wrap" id="icon-selector">
                                    <button type="button" class="icon-option-btn" data-icon="folder" title="Folder">
                                        <i class="fas fa-folder"></i>
                                    </button>
                                    <button type="button" class="icon-option-btn" data-icon="briefcase" title="Briefcase">
                                        <i class="fas fa-briefcase"></i>
                                    </button>
                                    <button type="button" class="icon-option-btn" data-icon="building" title="Building">
                                        <i class="fas fa-building"></i>
                                    </button>
                                    <button type="button" class="icon-option-btn" data-icon="users" title="Team">
                                        <i class="fas fa-users"></i>
                                    </button>
                                    <button type="button" class="icon-option-btn" data-icon="project-diagram" title="Project">
                                        <i class="fas fa-project-diagram"></i>
                                    </button>
                                    <button type="button" class="icon-option-btn" data-icon="folder-open" title="Open Folder">
                                        <i class="fas fa-folder-open"></i>
                                    </button>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">Selected: </small>
                                    <span id="icon-preview" style="font-size: 1.5rem;">
                                        <i class="fas fa-{{ $drive->icon ?? 'folder' }}" id="preview-icon" style="color: {{ $drive->color ?? '#31d8b2' }};"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($drive->type === 'shared' && $drive->owner_id === auth()->id())
                    <div class="mb-3">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> Only the drive owner can delete this drive. Changes to drive settings will affect all members.
                        </div>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('drives.show', $drive) }}" class="btn btn-secondary">Cancel</a>
                        <div>
                            @if($drive->owner_id === auth()->id())
                                <button type="button" class="btn btn-danger me-2" onclick="confirmDelete()">
                                    <i class="fas fa-trash me-1"></i>Delete Drive
                                </button>
                            @endif
                            <button type="submit" class="btn btn-primary">Update Drive</button>
                        </div>
                    </div>
                </form>

                @if($drive->owner_id === auth()->id())
                <form id="delete-form" action="{{ route('drives.destroy', $drive) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
                @endif
            </div>

            @if($drive->type === 'shared')
            <!-- Members Section -->
            <div class="dashboard-card mt-4">
                <h4 class="mb-3 brand-teal">
                    <i class="fas fa-users me-2"></i>Drive Members
                </h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                @if($drive->isOwnerOrAdmin(auth()->user()))
                                    <th>Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($drive->users as $member)
                                <tr>
                                    <td>
                                        {{ $member->name }}
                                        @if($member->id === $drive->owner_id)
                                            <span class="badge bg-brand-teal ms-2">Owner</span>
                                        @endif
                                    </td>
                                    <td>{{ $member->email }}</td>
                                    <td>
                                        @if($drive->isOwnerOrAdmin(auth()->user()) && $member->id !== $drive->owner_id)
                                            <select class="form-select form-select-sm" onchange="updateRole({{ $member->id }}, this.value)">
                                                @foreach(['admin', 'member', 'viewer'] as $role)
                                                    <option value="{{ $role }}" {{ $drive->getUserRole($member) === $role ? 'selected' : '' }}>
                                                        {{ ucfirst($role) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            {{ ucfirst($drive->getUserRole($member)) }}
                                        @endif
                                    </td>
                                    @if($drive->isOwnerOrAdmin(auth()->user()) && $member->id !== auth()->id())
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                @if($drive->owner_id === auth()->id())
                                                    <button class="btn btn-sm btn-outline-warning" onclick="transferOwnership({{ $member->id }}, '{{ $member->name }}')" title="Transfer Ownership">
                                                        <i class="fas fa-crown"></i>
                                                    </button>
                                                @endif
                                                <button class="btn btn-sm btn-outline-danger" onclick="removeMember({{ $member->id }}, '{{ $member->name }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    @else
                                        <td></td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($drive->isOwnerOrAdmin(auth()->user()))
                <!-- Invite User Form -->
                <div class="mt-4 pt-4 border-top">
                    <h5 class="mb-3">Invite User</h5>
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->has('email'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first('email') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    <form action="{{ route('drives.members.invite', $drive) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" name="email" placeholder="User email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <select class="form-select" name="role" required>
                                    <option value="member" selected>Member</option>
                                    <option value="admin">Admin</option>
                                    <option value="viewer">Viewer</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <button type="submit" class="btn btn-primary w-100">Invite</button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif
            </div>
            @endif

            @if($drive->type === 'shared' && $drive->isOwnerOrAdmin(auth()->user()) && !$drive->isSubDrive())
            <!-- Sub-Drives Section -->
            <div class="dashboard-card mt-4">
                <h4 class="mb-3 brand-teal">
                    <i class="fas fa-folder-tree me-2"></i>Sub-Drives
                    <small class="text-muted">(Teams & Departments)</small>
                </h4>
                <p class="text-muted mb-3">
                    Create sub-drives to organize different teams or departments within your organization. 
                    Sub-drive transactions will appear in your main drive reports.
                </p>

                @if(session('sub_drive_success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>{{ session('sub_drive_success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($drive->subDrives->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Owner</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($drive->subDrives as $subDrive)
                                <tr>
                                    <td>
                                        <i class="fas fa-{{ $subDrive->icon ?? 'folder' }} me-2" style="color: {{ $subDrive->color ?? '#31d8b2' }};"></i>
                                        <a href="{{ route('drives.show', $subDrive) }}" class="text-decoration-none">{{ $subDrive->name }}</a>
                                    </td>
                                    <td>{{ $subDrive->description }}</td>
                                    <td>{{ $subDrive->owner->name }}</td>
                                    <td>
                                        <a href="{{ route('drives.show', $subDrive) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Create Sub-Drive Form -->
                <div class="mt-4 pt-4 border-top">
                    <h5 class="mb-3">Create New Sub-Drive</h5>
                    <form action="{{ route('drives.sub-drives.store', $drive) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" class="form-control" name="name" placeholder="Sub-drive name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <input type="text" class="form-control" name="description" placeholder="Description (optional)" value="{{ old('description') }}">
                            </div>
                            <div class="col-md-2 mb-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-plus me-1"></i>Create
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <input type="color" class="form-control form-control-color" name="color" value="{{ old('color', '#31d8b2') }}" title="Choose color">
                            </div>
                            <div class="col-md-3 mb-3">
                                <select class="form-select" name="icon">
                                    <option value="folder" {{ old('icon') === 'folder' ? 'selected' : '' }}>Folder</option>
                                    <option value="building" {{ old('icon') === 'building' ? 'selected' : '' }}>Building</option>
                                    <option value="users" {{ old('icon') === 'users' ? 'selected' : '' }}>Team</option>
                                    <option value="briefcase" {{ old('icon') === 'briefcase' ? 'selected' : '' }}>Briefcase</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Hide Sub-Drives Settings -->
            <div class="dashboard-card mt-4">
                <h4 class="mb-3 brand-teal">
                    <i class="fas fa-cog me-2"></i>Sub-Drive Visibility
                </h4>
                <p class="text-muted mb-3">
                    Hide specific sub-drives from appearing in your main drive reports. 
                    This does not affect the sub-drive's own data.
                </p>

                <form action="{{ route('drives.settings.update', $drive) }}" method="POST">
                    @csrf
                    @php
                        $hiddenSubDrives = $drive->settings['hidden_sub_drives'] ?? [];
                    @endphp

                    @if($drive->subDrives->count() > 0)
                        @foreach($drive->subDrives as $subDrive)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="hidden_sub_drives[]" 
                                       value="{{ $subDrive->id }}" id="hide_{{ $subDrive->id }}"
                                       {{ in_array($subDrive->id, $hiddenSubDrives) ? 'checked' : '' }}>
                                <label class="form-check-label" for="hide_{{ $subDrive->id }}">
                                    {{ $subDrive->name }}
                                </label>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No sub-drives to manage</p>
                    @endif

                    @if($drive->subDrives->count() > 0)
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Visibility
                            </button>
                        </div>
                    @endif
                </form>
            </div>
            @endif

            <!-- Role Assignments Section -->
            @if($drive->type === 'shared' && $drive->isOwnerOrAdmin(auth()->user()))
            <div class="dashboard-card mt-4">
                <h4 class="mb-3 brand-teal">
                    <i class="fas fa-user-shield me-2"></i>Role Assignments
                </h4>
                <p class="text-muted small mb-3">
                    Assign roles to people and users to control their access to different apps and features within this Drive.
                    Roles are managed separately in <a href="{{ route('drives.roles.index', $drive) }}">Roles & Permissions</a>.
                </p>
                
                @if(isset($roles) && $roles->count() > 0)
                    <!-- Assign roles to People -->
                    @if($people->count() > 0)
                        <div class="mb-4">
                            <h5 class="mb-3">People</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Current Role</th>
                                            <th>Assign Role</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($people as $person)
                                            @php
                                                $currentRole = $drive->getRoleForPerson($person);
                                            @endphp
                                            <tr>
                                                <td>
                                                    {{ $person->full_name }}
                                                    @if($person->user_id)
                                                        <span class="badge bg-info ms-1">Linked User</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($currentRole)
                                                        <span class="badge bg-primary">{{ $currentRole->name }}</span>
                                                    @else
                                                        <span class="text-muted">No role</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <form action="{{ route('drives.roles.assign', $drive) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="assignable_type" value="App\Models\Person">
                                                        <input type="hidden" name="assignable_id" value="{{ $person->id }}">
                                                        <select name="drive_role_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                                            <option value="">No role</option>
                                                            @foreach($roles as $role)
                                                                <option value="{{ $role->id }}" {{ $currentRole && $currentRole->id == $role->id ? 'selected' : '' }}>
                                                                    {{ $role->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Assign roles to Drive Users (excluding owner) -->
                    @if($driveUsers->count() > 0)
                        <div class="mb-4">
                            <h5 class="mb-3">Drive Users</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Legacy Role</th>
                                            <th>Assigned Role</th>
                                            <th>Assign Role</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($driveUsers as $user)
                                            @php
                                                $currentRole = $drive->getRoleForUser($user);
                                                $legacyRole = $drive->getUserRole($user);
                                            @endphp
                                            <tr>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ ucfirst($legacyRole) }}</span>
                                                </td>
                                                <td>
                                                    @if($currentRole)
                                                        <span class="badge bg-primary">{{ $currentRole->name }}</span>
                                                    @else
                                                        <span class="text-muted">No role</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <form action="{{ route('drives.roles.assign', $drive) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="assignable_type" value="App\Models\User">
                                                        <input type="hidden" name="assignable_id" value="{{ $user->id }}">
                                                        <select name="drive_role_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                                            <option value="">No role</option>
                                                            @foreach($roles as $role)
                                                                <option value="{{ $role->id }}" {{ $currentRole && $currentRole->id == $role->id ? 'selected' : '' }}>
                                                                    {{ $role->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                    
                    @if($people->count() === 0 && $driveUsers->count() === 0)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No people or users available to assign roles. Add people in People Manager or invite users to the Drive.
                        </div>
                    @endif
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No roles have been created yet. 
                        <a href="{{ route('drives.roles.index', $drive) }}">Create roles</a> to assign permissions.
                    </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.icon-option-btn {
    width: 48px;
    height: 48px;
    border: 2px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, 0.7);
    cursor: pointer;
    transition: all 0.3s ease;
    padding: 0;
}

.icon-option-btn:hover {
    border-color: #31d8b2;
    background: rgba(49, 216, 178, 0.1);
    color: #31d8b2;
    transform: scale(1.1);
}

.icon-option-btn.active {
    border-color: #31d8b2;
    background: rgba(49, 216, 178, 0.2);
    color: #31d8b2;
}

[data-theme="light"] .icon-option-btn {
    background: #ffffff;
    border-color: #dee2e6;
    color: #495057;
}

[data-theme="light"] .icon-option-btn:hover {
    border-color: #31d8b2;
    background: rgba(49, 216, 178, 0.1);
    color: #31d8b2;
}

[data-theme="light"] .icon-option-btn.active {
    border-color: #31d8b2;
    background: rgba(49, 216, 178, 0.15);
    color: #31d8b2;
}
</style>
@endpush

@push('scripts')
<script>
// Icon preview
const iconSelect = document.getElementById('icon');
const colorInput = document.getElementById('color');
const previewIcon = document.getElementById('preview-icon');
const iconButtons = document.querySelectorAll('.icon-option-btn');

const iconMap = {
    'folder': 'fa-folder',
    'briefcase': 'fa-briefcase',
    'building': 'fa-building',
    'users': 'fa-users',
    'project-diagram': 'fa-project-diagram',
    'folder-open': 'fa-folder-open'
};

function updateIconPreview() {
    if (!iconSelect || !previewIcon) return;
    
    const selectedIcon = iconSelect.value;
    const iconClass = iconMap[selectedIcon] || 'fa-folder';
    previewIcon.className = 'fas ' + iconClass;
    if (colorInput) {
        previewIcon.style.color = colorInput.value;
    }
    
    // Update active state on buttons
    iconButtons.forEach(btn => {
        if (btn.dataset.icon === selectedIcon) {
            btn.classList.add('active');
            if (colorInput) {
                btn.style.color = colorInput.value;
                btn.style.borderColor = colorInput.value;
            }
        } else {
            btn.classList.remove('active');
            btn.style.color = '';
            btn.style.borderColor = '';
        }
    });
}

if (iconSelect && colorInput && previewIcon) {
    iconSelect.addEventListener('change', updateIconPreview);
    colorInput.addEventListener('input', updateIconPreview);
    
    // Icon button click handlers
    iconButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            iconSelect.value = this.dataset.icon;
            updateIconPreview();
        });
    });
    
    updateIconPreview();
}

function confirmDelete() {
    if (confirm('Are you sure you want to delete this drive? This action cannot be undone and will delete all items in the drive.')) {
        document.getElementById('delete-form').submit();
    }
}

function updateRole(userId, role) {
    if (confirm('Change this member\'s role to ' + role + '?')) {
        const url = `{{ url('drives') }}/{{ $drive->id }}/members/${userId}/role`;
        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ role: role })
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                location.reload();
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update role. Please try again.');
        });
    }
}

function removeMember(userId, userName) {
    if (confirm(`Remove ${userName} from this drive?`)) {
        const url = `{{ url('drives') }}/{{ $drive->id }}/members/${userId}`;
        fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                location.reload();
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to remove member. Please try again.');
        });
    }
}

function transferOwnership(userId, userName) {
    if (confirm(`Transfer ownership of this drive to ${userName}? This will make them the owner and downgrade you to admin. This action cannot be undone.`)) {
        const url = `{{ url('drives') }}/{{ $drive->id }}/members/${userId}/transfer-ownership`;
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ confirm_transfer: true })
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                location.reload();
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to transfer ownership. Please try again.');
        });
    }
}
</script>
@endpush
@endsection

