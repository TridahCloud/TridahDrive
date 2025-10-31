@extends('layouts.dashboard')

@section('title', 'Edit Task - ' . $drive->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-2">
                                <li class="breadcrumb-item"><a href="{{ route('drives.index') }}">Drives</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('drives.show', $drive) }}">{{ $drive->name }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('drives.projects.projects.index', $drive) }}">Projects</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('drives.projects.projects.show', [$drive, $project]) }}">{{ $project->name }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('drives.projects.projects.tasks.show', [$drive, $project, $task]) }}">{{ $task->title }}</a></li>
                                <li class="breadcrumb-item active">Edit</li>
                            </ol>
                        </nav>
                        <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Task</h4>
                    </div>
                    <a href="{{ route('drives.projects.projects.tasks.show', [$drive, $project, $task]) }}" class="btn btn-outline-secondary">
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

                <form action="{{ route('drives.projects.projects.tasks.update', [$drive, $project, $task]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $task->title) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $task->description) }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="todo" {{ old('status', $task->status) === 'todo' ? 'selected' : '' }}>Todo</option>
                                <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="review" {{ old('status', $task->status) === 'review' ? 'selected' : '' }}>Review</option>
                                <option value="done" {{ old('status', $task->status) === 'done' ? 'selected' : '' }}>Done</option>
                                <option value="blocked" {{ old('status', $task->status) === 'blocked' ? 'selected' : '' }}>Blocked</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="low" {{ old('priority', $task->priority) === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority', $task->priority) === 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ old('priority', $task->priority) === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="owner_id" class="form-label">Owner (Responsible Person)</label>
                            <select class="form-select" id="owner_id" name="owner_id">
                                <option value="">None</option>
                                @foreach($driveMembers as $member)
                                    <option value="{{ $member->id }}" {{ old('owner_id', $task->owner_id) == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Who is responsible for this task</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="parent_id" class="form-label">Parent Task</label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="">None</option>
                                @foreach($parentTasks as $parentTask)
                                    <option value="{{ $parentTask->id }}" {{ old('parent_id', $task->parent_id) == $parentTask->id ? 'selected' : '' }}>
                                        {{ $parentTask->title }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Make this a subtask</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', $task->start_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="estimated_hours" class="form-label">Estimated Hours</label>
                            <input type="number" class="form-control" id="estimated_hours" name="estimated_hours" min="0" step="0.5" value="{{ old('estimated_hours', $task->estimated_hours) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="actual_hours" class="form-label">Actual Hours</label>
                            <input type="number" class="form-control" id="actual_hours" name="actual_hours" min="0" step="0.5" value="{{ old('actual_hours', $task->actual_hours) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="member_ids" class="form-label">Assign Members</label>
                            <select class="form-select" id="member_ids" name="member_ids[]" multiple>
                                @foreach($driveMembers as $member)
                                    <option value="{{ $member->id }}" {{ in_array($member->id, old('member_ids', $task->members->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple members</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="label_ids" class="form-label">Labels</label>
                            <select class="form-select" id="label_ids" name="label_ids[]" multiple>
                                @foreach($labels as $label)
                                    <option value="{{ $label->id }}" {{ in_array($label->id, old('label_ids', $task->labels->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $label->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple labels</small>
                        </div>
                    </div>

                    @if($task->attachments->where('type', 'header')->count() > 0)
                        <div class="mb-3">
                            <label class="form-label">Current Header Image</label>
                            @foreach($task->attachments->where('type', 'header') as $headerImage)
                                <div class="mb-2">
                                    <img src="{{ route('drives.projects.projects.tasks.attachments.show', [$drive, $project, $task, $headerImage]) }}" 
                                         alt="Current header" 
                                         class="img-thumbnail" 
                                         style="max-height: 150px;">
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="header_image" class="form-label">Header Image (for Kanban Card)</label>
                        <input type="file" class="form-control" id="header_image" name="header_image" accept="image/*">
                        <small class="text-muted">Optional. Maximum file size: 10MB. Leave empty to keep current image.</small>
                    </div>

                    @if($task->attachments->where('type', 'attachment')->count() > 0)
                        <div class="mb-3">
                            <label class="form-label">Current Attachments</label>
                            <div class="row">
                                @foreach($task->attachments->where('type', 'attachment') as $attachment)
                                    <div class="col-md-3 mb-2">
                                        <div class="card">
                                            <div class="card-body p-2">
                                                @if(str_starts_with($attachment->mime_type, 'image/'))
                                                    <img src="{{ route('drives.projects.projects.tasks.attachments.show', [$drive, $project, $task, $attachment]) }}" 
                                                         alt="{{ $attachment->original_filename }}" 
                                                         class="img-thumbnail" 
                                                         style="max-height: 100px; width: 100%; object-fit: cover;">
                                                @else
                                                    <i class="fas fa-file fa-2x text-muted"></i>
                                                @endif
                                                <small class="d-block text-truncate" title="{{ $attachment->original_filename }}">
                                                    {{ $attachment->original_filename }}
                                                </small>
                                                <small class="text-muted">{{ $attachment->human_readable_size }}</small>
                                                <form action="{{ route('drives.projects.projects.tasks.attachments.destroy', [$drive, $project, $task, $attachment]) }}" 
                                                      method="POST" 
                                                      class="d-inline mt-1"
                                                      onsubmit="return confirm('Are you sure you want to delete this attachment?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="attachments" class="form-label">Add Attachments</label>
                        <input type="file" class="form-control" id="attachments" name="attachments[]" multiple accept="image/*,.pdf,.doc,.docx">
                        <small class="text-muted">You can upload multiple files. Maximum file size: 10MB per file</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Task
                        </button>
                        <a href="{{ route('drives.projects.projects.tasks.show', [$drive, $project, $task]) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

