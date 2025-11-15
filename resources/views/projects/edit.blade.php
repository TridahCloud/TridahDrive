@extends('layouts.dashboard')

@section('title', 'Edit Project - ' . $drive->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('drives.index') }}">Drives</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('drives.show', $drive) }}">{{ $drive->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('drives.projects.projects.index', $drive) }}">Projects</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('drives.projects.projects.show', [$drive, $project]) }}">{{ $project->name }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
            <h1 class="display-6 mb-0 brand-teal">Edit Project</h1>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="dashboard-card">
                <form action="{{ route('drives.projects.projects.update', [$drive, $project]) }}" method="POST" enctype="multipart/form-data" id="projectForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Project Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $project->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $project->description) }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="color" class="form-label">Color</label>
                            <input type="color" class="form-control form-control-color" id="color" name="color" value="{{ old('color', $project->color) }}" title="Choose color">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" {{ old('status', $project->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="archived" {{ old('status', $project->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                                <option value="completed" {{ old('status', $project->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="header_image" class="form-label">Header Image</label>
                        @if($project->header_image_path)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $project->header_image_path) }}" alt="Current header" class="img-thumbnail" style="max-height: 150px;">
                            </div>
                        @endif
                        <input type="file" class="form-control" id="header_image" name="header_image" accept="image/*">
                        <small class="text-muted">Optional. Maximum file size: 10MB. Leave empty to keep current image.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1" {{ old('is_public', $project->is_public) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_public">
                                Make this project public
                            </label>
                            <small class="form-text text-muted d-block">Public projects can be viewed by anyone with the link</small>
                        </div>
                        @if($project->is_public && $project->public_key)
                            <div class="mt-2">
                                <label class="form-label">Public Link</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" value="{{ route('projects.public.show', $project->public_key) }}" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.value); this.textContent='Copied!'; setTimeout(() => this.textContent='Copy', 2000);">
                                        Copy
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Project
                        </button>
                        <a href="{{ route('drives.projects.projects.show', [$drive, $project]) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="dashboard-card mb-4">
                <h5 class="mb-3">Quick Actions</h5>
                <a href="{{ route('drives.projects.projects.show', [$drive, $project]) }}" class="btn btn-secondary w-100 mb-2">Cancel</a>
                <button type="submit" form="projectForm" class="btn btn-primary w-100">
                    <i class="fas fa-save me-2"></i>Update Project
                </button>
            </div>
            
            <!-- Assigned Users Section -->
            <div class="dashboard-card">
                <h5 class="mb-3">
                    <i class="fas fa-users me-2 brand-teal"></i>Shared With
                </h5>
                <p class="text-muted small mb-3">Add users to this project and set their permissions. Users don't need to be members of the drive.</p>
                
                <!-- Add User by Email -->
                <div class="mb-3">
                    <label for="user-email-search" class="form-label small">Add User by Email</label>
                    <div class="input-group">
                        <input type="email" class="form-control" id="user-email-search" placeholder="user@example.com">
                        <button class="btn btn-outline-primary" type="button" id="search-user-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div id="user-search-result" class="mt-2"></div>
                </div>
                
                <form id="assignPeopleForm" onsubmit="return handleAssignPeopleSubmit(event)">
                    @csrf
                    
                    <div id="assigned-users-list" class="mb-3">
                        <div class="list-group" style="max-height: 400px; overflow-y: auto;">
                            @foreach($project->users as $user)
                                <div class="list-group-item" data-user-id="{{ $user->id }}">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">{{ $user->name }}</div>
                                            @if($user->email)
                                                <small class="text-muted">{{ $user->email }}</small>
                                            @endif
                                        </div>
                                        <div class="ms-3">
                                            <select name="users[{{ $user->id }}][role]" class="form-select form-select-sm" style="width: auto;">
                                                <option value="viewer" {{ $user->pivot->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                                <option value="editor" {{ $user->pivot->role === 'editor' ? 'selected' : '' }}>Editor</option>
                                            </select>
                                            <input type="hidden" name="users[{{ $user->id }}][id]" value="{{ $user->id }}">
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-2 remove-user-btn" data-user-id="{{ $user->id }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                            
                            @if(isset($availableUsers) && $availableUsers->count() > 0)
                                @foreach($availableUsers as $user)
                                    @if(!$project->users->contains($user->id))
                                        <div class="list-group-item" data-user-id="{{ $user->id }}">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold">{{ $user->name }}</div>
                                                    @if($user->email)
                                                        <small class="text-muted">{{ $user->email }}</small>
                                                    @endif
                                                    <small class="badge bg-info ms-2">Drive Member</small>
                                                </div>
                                                <div class="ms-3">
                                                    <select name="users[{{ $user->id }}][role]" class="form-select form-select-sm" style="width: auto;">
                                                        <option value="viewer">Viewer</option>
                                                        <option value="editor">Editor</option>
                                                    </select>
                                                    <input type="hidden" name="users[{{ $user->id }}][id]" value="{{ $user->id }}">
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger ms-2 remove-user-btn" data-user-id="{{ $user->id }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100" id="saveAssignmentsBtn">
                        <i class="fas fa-save me-2"></i>Save Assignments
                    </button>
                </form>
            </div>
            
            @push('scripts')
            <script>
                function handleAssignPeopleSubmit(event) {
                    event.preventDefault();
                    const form = event.target;
                    const formData = new FormData(form);
                    const submitBtn = document.getElementById('saveAssignmentsBtn');
                    const originalBtnText = submitBtn.innerHTML;
                    
                    // Disable button and show loading
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
                    
                    // Collect all user data
                    const users = {};
                    const userInputs = form.querySelectorAll('input[name^="users["][name$="[id]"]');
                    userInputs.forEach(input => {
                        const userId = input.value;
                        const roleInput = form.querySelector(`input[name="users[${userId}][role]"], select[name="users[${userId}][role]"]`);
                        if (userId && roleInput) {
                            users[userId] = {
                                id: userId,
                                role: roleInput.value || roleInput.options[roleInput.selectedIndex].value
                            };
                        }
                    });
                    
                    // Convert to array format
                    const usersArray = Object.values(users);
                    
                    // Submit via fetch to avoid service worker issues
                    fetch('{{ route("drives.projects.projects.assign-people", [$drive, $project]) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ users: usersArray })
                    })
                    .then(async response => {
                        // Check if response is ok
                        if (!response.ok) {
                            let errorMessage = 'An error occurred';
                            try {
                                const errorData = await response.json();
                                errorMessage = errorData.message || errorData.error || errorMessage;
                            } catch (e) {
                                // If response is not JSON, try to get text
                                try {
                                    errorMessage = await response.text() || errorMessage;
                                } catch (e2) {
                                    errorMessage = `Server error: ${response.status} ${response.statusText}`;
                                }
                            }
                            throw new Error(errorMessage);
                        }
                        
                        // Try to parse JSON, but handle non-JSON responses
                        try {
                            const data = await response.json();
                            return data;
                        } catch (e) {
                            // If not JSON, assume success
                            return { success: true };
                        }
                    })
                    .then(data => {
                        // Success - reload page to show updated assignments
                        window.location.reload();
                    })
                    .catch(error => {
                        console.error('Error saving assignments:', error);
                        
                        // Show error message
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
                        errorDiv.innerHTML = `
                            <strong>Error:</strong> ${error.message || 'Failed to save assignments. Please try again.'}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        `;
                        form.insertAdjacentElement('afterend', errorDiv);
                        
                        // Re-enable button
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    });
                    
                    return false;
                }
                
                document.addEventListener('DOMContentLoaded', function() {
                    const searchBtn = document.getElementById('search-user-btn');
                    const emailInput = document.getElementById('user-email-search');
                    const resultDiv = document.getElementById('user-search-result');
                    const assignedUsersList = document.getElementById('assigned-users-list');
                    const form = document.getElementById('assignPeopleForm');
                    
                    // Remove user button handlers
                    document.querySelectorAll('.remove-user-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const userId = this.dataset.userId;
                            const userItem = document.querySelector(`[data-user-id="${userId}"]`);
                            if (userItem) {
                                userItem.remove();
                            }
                        });
                    });
                    
                    // Search user by email
                    searchBtn.addEventListener('click', function() {
                        const email = emailInput.value.trim();
                        if (!email) {
                            resultDiv.innerHTML = '<div class="alert alert-warning">Please enter an email address.</div>';
                            return;
                        }
                        
                        searchBtn.disabled = true;
                        searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                        
                        fetch('{{ route("drives.projects.projects.search-users", [$drive, $project]) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ email: email })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                resultDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                            } else if (data.user) {
                                // Check if user is already in the list
                                const existingUser = document.querySelector(`[data-user-id="${data.user.id}"]`);
                                if (existingUser) {
                                    resultDiv.innerHTML = '<div class="alert alert-warning">User is already added to this project.</div>';
                                } else {
                                    // Add user to the list
                                    const userHtml = `
                                        <div class="list-group-item" data-user-id="${data.user.id}">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold">${data.user.name}</div>
                                                    <small class="text-muted">${data.user.email}</small>
                                                </div>
                                                <div class="ms-3">
                                                    <select name="users[${data.user.id}][role]" class="form-select form-select-sm" style="width: auto;">
                                                        <option value="viewer">Viewer</option>
                                                        <option value="editor">Editor</option>
                                                    </select>
                                                    <input type="hidden" name="users[${data.user.id}][id]" value="${data.user.id}">
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger ms-2 remove-user-btn" data-user-id="${data.user.id}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    `;
                                    assignedUsersList.querySelector('.list-group').insertAdjacentHTML('beforeend', userHtml);
                                    
                                    // Add remove handler to new button
                                    const newRemoveBtn = assignedUsersList.querySelector(`[data-user-id="${data.user.id}"] .remove-user-btn`);
                                    newRemoveBtn.addEventListener('click', function() {
                                        const userId = this.dataset.userId;
                                        const userItem = document.querySelector(`[data-user-id="${userId}"]`);
                                        if (userItem) {
                                            userItem.remove();
                                        }
                                    });
                                    
                                    resultDiv.innerHTML = '<div class="alert alert-success">User added successfully!</div>';
                                    emailInput.value = '';
                                    
                                    setTimeout(() => {
                                        resultDiv.innerHTML = '';
                                    }, 3000);
                                }
                            }
                        })
                        .catch(error => {
                            resultDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
                        })
                        .finally(() => {
                            searchBtn.disabled = false;
                            searchBtn.innerHTML = '<i class="fas fa-search"></i>';
                        });
                    });
                    
                    // Allow Enter key to trigger search
                    emailInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            searchBtn.click();
                        }
                    });
                });
            </script>
            @endpush
        </div>
    </div>
</div>
@endsection

