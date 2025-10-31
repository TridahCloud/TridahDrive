@extends('layouts.dashboard')

@section('title', 'Create Task Label - ' . $drive->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-2">
                                <li class="breadcrumb-item"><a href="{{ route('drives.index') }}">Drives</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('drives.show', $drive) }}">{{ $drive->name }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('drives.projects.task-labels.index', $drive) }}">Task Labels</a></li>
                                <li class="breadcrumb-item active">Create</li>
                            </ol>
                        </nav>
                        <h4 class="mb-0"><i class="fas fa-plus me-2"></i>Create Task Label</h4>
                    </div>
                    <a href="{{ route('drives.projects.task-labels.index', $drive) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('drives.projects.task-labels.store', $drive) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Label Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="color" class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" id="color" name="color" value="{{ old('color', '#6366F1') }}" title="Choose color">
                        <small class="text-muted">Select a color for this label</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Label
                        </button>
                        <a href="{{ route('drives.projects.task-labels.index', $drive) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

