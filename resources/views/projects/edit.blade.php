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
            @if(isset($availableUsers))
            <div class="dashboard-card">
                <h5 class="mb-3">
                    <i class="fas fa-users me-2 brand-teal"></i>Assigned Users
                </h5>
                <p class="text-muted small mb-3">Assign users from your Drive to this project.</p>
                
                <form action="{{ route('drives.projects.projects.assign-people', [$drive, $project]) }}" method="POST" id="assignPeopleForm">
                    @csrf
                    
                    @if($availableUsers->count() > 0)
                        <div class="mb-3">
                            <div class="list-group" style="max-height: 400px; overflow-y: auto;">
                                @foreach($availableUsers as $user)
                                    <label class="list-group-item list-group-item-action">
                                        <div class="d-flex align-items-center">
                                            <input type="checkbox" 
                                                   class="form-check-input me-3" 
                                                   name="user_ids[]" 
                                                   value="{{ $user->id }}"
                                                   {{ $project->users->contains($user->id) ? 'checked' : '' }}>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold">{{ $user->name }}</div>
                                                @if($user->email)
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i>Save Assignments
                        </button>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No users available in this Drive.
                        </div>
                    @endif
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

