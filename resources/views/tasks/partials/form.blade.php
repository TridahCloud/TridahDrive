@php
    $isEdit = isset($task) && $task && $task->exists;
    $formAction = $isEdit 
        ? route('drives.projects.projects.tasks.update', [$drive, $project, $task])
        : route('drives.projects.projects.tasks.store', [$drive, $project]);
    $formMethod = $isEdit ? 'PUT' : 'POST';
    $submitButtonText = $isEdit ? 'Update Task' : 'Create Task';
    $submitButtonIcon = $isEdit ? 'fa-save' : 'fa-save';
    $cancelUrl = $isEdit 
        ? route('drives.projects.projects.tasks.show', [$drive, $project, $task])
        : route('drives.projects.projects.show', [$drive, $project]);
    $task = $task ?? null;
@endphp

<form id="taskForm" action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $task && $task->title ? $task->title : '') }}" required>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <div id="descriptionEditor"></div>
        <input type="hidden" id="description" name="description">
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="status_id" class="form-label">Status <span class="text-danger">*</span></label>
            <select class="form-select" id="status_id" name="status_id" required>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}" 
                        {{ (int)old('status_id', $task && $task->task_status_id ? $task->task_status_id : ($statuses->first()?->id ?? '')) === $status->id ? 'selected' : '' }}>
                        {{ $status->name }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Statuses are managed in the project board.</small>
        </div>
        <div class="col-md-6 mb-3">
            <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
            <select class="form-select" id="priority" name="priority" required>
                @php
                    $priority = old('priority', $task && $task->priority ? $task->priority : 'medium');
                @endphp
                <option value="low" {{ $priority === 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ $priority === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ $priority === 'high' ? 'selected' : '' }}>High</option>
                <option value="urgent" {{ $priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="owner_id" class="form-label">Owner (Responsible Person)</label>
            <select class="form-select" id="owner_id" name="owner_id">
                <option value="">None</option>
                @foreach($driveMembers as $member)
                    <option value="{{ $member->id }}" {{ old('owner_id', $task && $task->owner_id ? $task->owner_id : '') == $member->id ? 'selected' : '' }}>
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
                    <option value="{{ $parentTask->id }}" {{ old('parent_id', $task && $task->parent_id ? $task->parent_id : '') == $parentTask->id ? 'selected' : '' }}>
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
            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', $task && $task->start_date ? $task->start_date->format('Y-m-d') : '') }}">
        </div>
        <div class="col-md-6 mb-3">
            <label for="due_date" class="form-label">Due Date</label>
            <input type="date" class="form-control" id="due_date" name="due_date" value="{{ old('due_date', $task && $task->due_date ? $task->due_date->format('Y-m-d') : '') }}">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="estimated_hours" class="form-label">Estimated Hours</label>
            <input type="number" class="form-control" id="estimated_hours" name="estimated_hours" min="0" step="0.5" value="{{ old('estimated_hours', $task && $task->estimated_hours ? $task->estimated_hours : '') }}">
        </div>
        @if($isEdit && $task)
        <div class="col-md-6 mb-3">
            <label for="actual_hours" class="form-label">Actual Hours</label>
            <input type="number" class="form-control" id="actual_hours" name="actual_hours" min="0" step="0.5" value="{{ old('actual_hours', $task->actual_hours ?? '') }}">
        </div>
        @endif
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="member_ids" class="form-label">Assign Members</label>
            <select class="form-select" id="member_ids" name="member_ids[]" multiple>
                @foreach($driveMembers as $member)
                    <option value="{{ $member->id }}" 
                        {{ in_array($member->id, old('member_ids', $task && $task->members ? $task->members->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
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
                    <option value="{{ $label->id }}" 
                        {{ in_array($label->id, old('label_ids', $task && $task->labels ? $task->labels->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                        {{ $label->name }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Hold Ctrl/Cmd to select multiple labels</small>
        </div>
    </div>

    @if(isset($customFieldDefinitions) && $customFieldDefinitions && $customFieldDefinitions->count() > 0)
        <div class="mb-4">
            <h5 class="mb-3"><i class="fas fa-list-alt me-2"></i>Custom Fields</h5>
            <div class="row">
                @foreach($customFieldDefinitions as $fieldDef)
                @php
                    $fieldValue = null;
                    if ($isEdit && $task && $task->customFieldValues) {
                        $value = $task->customFieldValues->firstWhere('field_definition_id', $fieldDef->id);
                        $fieldValue = $value ? $value->value : null;
                    }
                    $oldValue = old("custom_fields.{$fieldDef->id}", $fieldValue);
                @endphp
                    <div class="col-md-6 mb-3">
                        <label for="custom_field_{{ $fieldDef->id }}" class="form-label">
                            {{ $fieldDef->name }}
                            @if($fieldDef->required)
                                <span class="text-danger">*</span>
                            @endif
                        </label>
                        @if($fieldDef->type === 'text')
                            <input type="text" 
                                   class="form-control" 
                                   id="custom_field_{{ $fieldDef->id }}" 
                                   name="custom_fields[{{ $fieldDef->id }}]" 
                                   value="{{ $oldValue }}"
                                   {{ $fieldDef->required ? 'required' : '' }}>
                        @elseif($fieldDef->type === 'number')
                            <input type="number" 
                                   class="form-control" 
                                   id="custom_field_{{ $fieldDef->id }}" 
                                   name="custom_fields[{{ $fieldDef->id }}]" 
                                   value="{{ $oldValue }}"
                                   {{ $fieldDef->required ? 'required' : '' }}>
                        @elseif($fieldDef->type === 'date')
                            <input type="date" 
                                   class="form-control" 
                                   id="custom_field_{{ $fieldDef->id }}" 
                                   name="custom_fields[{{ $fieldDef->id }}]" 
                                   value="{{ $oldValue }}"
                                   {{ $fieldDef->required ? 'required' : '' }}>
                        @elseif($fieldDef->type === 'textarea')
                            <textarea class="form-control" 
                                      id="custom_field_{{ $fieldDef->id }}" 
                                      name="custom_fields[{{ $fieldDef->id }}]" 
                                      rows="3"
                                      {{ $fieldDef->required ? 'required' : '' }}>{{ $oldValue }}</textarea>
                        @elseif($fieldDef->type === 'select')
                            <select class="form-select" 
                                    id="custom_field_{{ $fieldDef->id }}" 
                                    name="custom_fields[{{ $fieldDef->id }}]"
                                    {{ $fieldDef->required ? 'required' : '' }}>
                                <option value="">Select an option...</option>
                                @if($fieldDef->options)
                                    @foreach($fieldDef->options as $option)
                                        <option value="{{ $option }}" {{ $oldValue === $option ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        @elseif($fieldDef->type === 'checkbox')
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="custom_field_{{ $fieldDef->id }}" 
                                       name="custom_fields[{{ $fieldDef->id }}]" 
                                       value="1"
                                       {{ $oldValue == '1' || $oldValue === true || $oldValue === 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="custom_field_{{ $fieldDef->id }}">
                                    {{ $fieldDef->name }}
                                </label>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($isEdit && $task && $task->attachments && $task->attachments->where('type', 'header')->count() > 0)
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
        <small class="text-muted">
            Optional. Maximum file size: 10MB.
            @if($isEdit)
                Leave empty to keep current image.
            @endif
        </small>
    </div>

    @if($isEdit && $task && $task->attachments && $task->attachments->where('type', 'attachment')->count() > 0)
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
        <label for="attachments" class="form-label">
            @if($isEdit)
                Add Attachments
            @else
                Attachments
            @endif
        </label>
        <input type="file" class="form-control" id="attachments" name="attachments[]" multiple accept="image/*,.pdf,.doc,.docx">
        <small class="text-muted">You can upload multiple files. Maximum file size: 10MB per file</small>
    </div>

    @if($isEdit && $task)
        <!-- Task Dependencies -->
        <div class="mb-4">
            <h5 class="mb-3"><i class="fas fa-project-diagram me-2"></i>Task Dependencies</h5>
            
            <!-- Blocked By -->
            <div class="mb-3">
                <label class="form-label">Blocked By</label>
                <div id="blockedByList" class="mb-2">
                    @php
                        $blockedBy = $task->dependencies()->where('type', 'blocked_by')->with('dependsOnTask')->get();
                    @endphp
                    @if($blockedBy->count() > 0)
                        @foreach($blockedBy as $dependency)
                            <div class="d-flex align-items-center gap-2 mb-2 p-2 border rounded">
                                <a href="{{ route('drives.projects.projects.tasks.show', [$drive, $project, $dependency->dependsOnTask]) }}" class="flex-grow-1">
                                    {{ $dependency->dependsOnTask->title }}
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-dependency" data-dependency-id="{{ $dependency->id }}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted small mb-0">No blocking tasks</p>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <select class="form-select" id="blockedBySelect">
                        <option value="">Select a task...</option>
                        @foreach($project->tasks()->whereNull('deleted_at')->where('id', '!=', $task->id)->get() as $otherTask)
                            @php
                                $alreadyBlockedBy = $task->dependencies()->where('type', 'blocked_by')->where('depends_on_task_id', $otherTask->id)->exists();
                            @endphp
                            @if(!$alreadyBlockedBy)
                                <option value="{{ $otherTask->id }}">{{ $otherTask->title }}</option>
                            @endif
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-outline-primary" id="addBlockedByBtn">
                        <i class="fas fa-plus me-1"></i>Add
                    </button>
                </div>
            </div>

            <!-- Blocks -->
            <div class="mb-3">
                <label class="form-label">Blocks</label>
                <div id="blocksList" class="mb-2">
                    @php
                        $blocks = $task->dependencies()->where('type', 'blocks')->with('dependsOnTask')->get();
                    @endphp
                    @if($blocks->count() > 0)
                        @foreach($blocks as $dependency)
                            <div class="d-flex align-items-center gap-2 mb-2 p-2 border rounded">
                                <a href="{{ route('drives.projects.projects.tasks.show', [$drive, $project, $dependency->dependsOnTask]) }}" class="flex-grow-1">
                                    {{ $dependency->dependsOnTask->title }}
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-dependency" data-dependency-id="{{ $dependency->id }}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted small mb-0">This task doesn't block any other tasks</p>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <select class="form-select" id="blocksSelect">
                        <option value="">Select a task...</option>
                        @foreach($project->tasks()->whereNull('deleted_at')->where('id', '!=', $task->id)->get() as $otherTask)
                            @php
                                $alreadyBlocks = $task->dependencies()->where('type', 'blocks')->where('depends_on_task_id', $otherTask->id)->exists();
                            @endphp
                            @if(!$alreadyBlocks)
                                <option value="{{ $otherTask->id }}">{{ $otherTask->title }}</option>
                            @endif
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-outline-primary" id="addBlocksBtn">
                        <i class="fas fa-plus me-1"></i>Add
                    </button>
                </div>
            </div>

            <!-- Related -->
            <div class="mb-3">
                <label class="form-label">Related Tasks</label>
                <div id="relatedList" class="mb-2">
                    @php
                        $related = $task->dependencies()->where('type', 'related')->with('dependsOnTask')->get();
                    @endphp
                    @if($related->count() > 0)
                        @foreach($related as $dependency)
                            <div class="d-flex align-items-center gap-2 mb-2 p-2 border rounded">
                                <a href="{{ route('drives.projects.projects.tasks.show', [$drive, $project, $dependency->dependsOnTask]) }}" class="flex-grow-1">
                                    {{ $dependency->dependsOnTask->title }}
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-dependency" data-dependency-id="{{ $dependency->id }}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted small mb-0">No related tasks</p>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <select class="form-select" id="relatedSelect">
                        <option value="">Select a task...</option>
                        @foreach($project->tasks()->whereNull('deleted_at')->where('id', '!=', $task->id)->get() as $otherTask)
                            @php
                                $alreadyRelated = $task->dependencies()->where('type', 'related')->where('depends_on_task_id', $otherTask->id)->exists();
                            @endphp
                            @if(!$alreadyRelated)
                                <option value="{{ $otherTask->id }}">{{ $otherTask->title }}</option>
                            @endif
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-outline-primary" id="addRelatedBtn">
                        <i class="fas fa-plus me-1"></i>Add
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fas {{ $submitButtonIcon }} me-2"></i>{{ $submitButtonText }}
        </button>
        <a href="{{ $cancelUrl }}" class="btn btn-outline-secondary">
            Cancel
        </a>
    </div>
</form>

