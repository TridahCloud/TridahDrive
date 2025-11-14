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
    $checklistItems = $isEdit && $task ? $task->checklistItems : collect([]);
@endphp

<style>
    .form-section {
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .form-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--border-color);
        color: var(--text-color);
    }
    .form-section-title i {
        margin-right: 0.5rem;
        color: var(--primary-color, #0d6efd);
    }
    .dependency-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        background: var(--bg-secondary);
        border-radius: 6px;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    .dependency-item a {
        flex: 1;
        text-decoration: none;
        color: var(--text-color);
    }
    .dependency-item a:hover {
        text-decoration: underline;
    }
    .checklist-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: var(--bg-secondary);
        border-radius: 6px;
        margin-bottom: 0.5rem;
        cursor: move;
        transition: background-color 0.2s;
    }
    .checklist-item:hover {
        background: var(--bg-tertiary, #f0f0f0);
    }
    .checklist-item.dragging {
        opacity: 0.5;
    }
    .checklist-item.drag-over {
        border: 2px dashed var(--primary-color, #0d6efd);
    }
    .checklist-item input[type="text"] {
        flex: 1;
    }
    .checklist-item input[type="text"]:focus {
        cursor: text;
    }
    .checklist-item.dragging input[type="text"],
    .checklist-item.dragging input[type="checkbox"] {
        pointer-events: none;
    }
    .checklist-item-actions {
        display: flex;
        gap: 0.5rem;
    }
    .checklist-item .drag-handle {
        color: var(--text-muted, #6c757d);
        cursor: grab;
        font-size: 1rem;
    }
    .checklist-item .drag-handle:active {
        cursor: grabbing;
    }
</style>

<form id="taskForm" action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <!-- Basic Information -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="fas fa-info-circle"></i>Basic Information
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $task && $task->title ? $task->title : '') }}" required>
        </div>
        <div class="mb-0">
            <label for="description" class="form-label">Description</label>
            <div id="descriptionEditor"></div>
            <input type="hidden" id="description" name="description">
        </div>
    </div>

    <!-- Status & Priority -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="fas fa-flag"></i>Status & Priority
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
            <div class="col-md-6 mb-0">
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
    </div>

    <!-- Assignment -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="fas fa-users"></i>Assignment
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="owner_id" class="form-label">Owner</label>
                <select class="form-select" id="owner_id" name="owner_id">
                    <option value="">None</option>
                    @foreach($driveMembers as $member)
                        <option value="{{ $member->id }}" {{ old('owner_id', $task && $task->owner_id ? $task->owner_id : '') == $member->id ? 'selected' : '' }}>
                            {{ $member->name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Responsible person</small>
            </div>
            <div class="col-md-4 mb-3">
                <label for="member_ids" class="form-label">Assigned Members</label>
                <select class="form-select" id="member_ids" name="member_ids[]" multiple size="4">
                    @foreach($driveMembers as $member)
                        <option value="{{ $member->id }}" 
                            {{ in_array($member->id, old('member_ids', $task && $task->members ? $task->members->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                            {{ $member->name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
            </div>
            <div class="col-md-4 mb-0">
                <label for="label_ids" class="form-label">Labels</label>
                <select class="form-select" id="label_ids" name="label_ids[]" multiple size="4">
                    @foreach($labels as $label)
                        <option value="{{ $label->id }}" 
                            {{ in_array($label->id, old('label_ids', $task && $task->labels ? $task->labels->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                            {{ $label->name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
            </div>
        </div>
    </div>

    <!-- Dates & Time -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="fas fa-calendar-alt"></i>Dates & Time
        </div>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', $task && $task->start_date ? $task->start_date->format('Y-m-d') : '') }}">
            </div>
            <div class="col-md-3 mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="due_date" name="due_date" value="{{ old('due_date', $task && $task->due_date ? $task->due_date->format('Y-m-d') : '') }}">
            </div>
            <div class="col-md-3 mb-3">
                <label for="estimated_hours" class="form-label">Estimated Hours</label>
                <input type="number" class="form-control" id="estimated_hours" name="estimated_hours" min="0" step="0.5" value="{{ old('estimated_hours', $task && $task->estimated_hours ? $task->estimated_hours : '') }}">
            </div>
            @if($isEdit && $task)
            <div class="col-md-3 mb-0">
                <label for="actual_hours" class="form-label">Actual Hours</label>
                <input type="number" class="form-control" id="actual_hours" name="actual_hours" min="0" step="0.5" value="{{ old('actual_hours', $task->actual_hours ?? '') }}">
            </div>
            @else
            <div class="col-md-3 mb-0"></div>
            @endif
        </div>
    </div>

    <!-- Relationships -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="fas fa-project-diagram"></i>Relationships
        </div>
        <div class="row">
            <div class="col-md-6 mb-0">
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
            @if($isEdit && $task)
            <div class="col-md-6 mb-0">
                <label class="form-label">Dependencies</label>
                <div class="d-flex flex-column gap-2">
                    @php
                        $blockedBy = $task->dependencies()->where('type', 'blocked_by')->with('dependsOnTask')->get();
                        $blocks = $task->dependencies()->where('type', 'blocks')->with('dependsOnTask')->get();
                        $related = $task->dependencies()->where('type', 'related')->with('dependsOnTask')->get();
                        $totalDeps = $blockedBy->count() + $blocks->count() + $related->count();
                    @endphp
                    @if($totalDeps > 0)
                        @if($blockedBy->count() > 0)
                            <div>
                                <small class="text-muted d-block mb-1">Blocked by ({{ $blockedBy->count() }})</small>
                                @foreach($blockedBy as $dependency)
                                    <div class="dependency-item">
                                        <a href="{{ route('drives.projects.projects.tasks.show', [$drive, $project, $dependency->dependsOnTask]) }}">
                                            {{ $dependency->dependsOnTask->title }}
                                        </a>
                                        <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-dependency" data-dependency-id="{{ $dependency->id }}" title="Remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if($blocks->count() > 0)
                            <div>
                                <small class="text-muted d-block mb-1">Blocks ({{ $blocks->count() }})</small>
                                @foreach($blocks as $dependency)
                                    <div class="dependency-item">
                                        <a href="{{ route('drives.projects.projects.tasks.show', [$drive, $project, $dependency->dependsOnTask]) }}">
                                            {{ $dependency->dependsOnTask->title }}
                                        </a>
                                        <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-dependency" data-dependency-id="{{ $dependency->id }}" title="Remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if($related->count() > 0)
                            <div>
                                <small class="text-muted d-block mb-1">Related ({{ $related->count() }})</small>
                                @foreach($related as $dependency)
                                    <div class="dependency-item">
                                        <a href="{{ route('drives.projects.projects.tasks.show', [$drive, $project, $dependency->dependsOnTask]) }}">
                                            {{ $dependency->dependsOnTask->title }}
                                        </a>
                                        <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-dependency" data-dependency-id="{{ $dependency->id }}" title="Remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <p class="text-muted small mb-0">No dependencies</p>
                    @endif
                    <div class="d-flex gap-2 mt-2">
                        <select class="form-select form-select-sm" id="dependencySelect">
                            <option value="">Add dependency...</option>
                            <optgroup label="Blocked By">
                                @foreach($project->tasks()->whereNull('deleted_at')->where('id', '!=', $task->id)->get() as $otherTask)
                                    @php
                                        $alreadyBlockedBy = $task->dependencies()->where('type', 'blocked_by')->where('depends_on_task_id', $otherTask->id)->exists();
                                    @endphp
                                    @if(!$alreadyBlockedBy)
                                        <option value="{{ $otherTask->id }}" data-type="blocked_by">{{ $otherTask->title }}</option>
                                    @endif
                                @endforeach
                            </optgroup>
                            <optgroup label="Blocks">
                                @foreach($project->tasks()->whereNull('deleted_at')->where('id', '!=', $task->id)->get() as $otherTask)
                                    @php
                                        $alreadyBlocks = $task->dependencies()->where('type', 'blocks')->where('depends_on_task_id', $otherTask->id)->exists();
                                    @endphp
                                    @if(!$alreadyBlocks)
                                        <option value="{{ $otherTask->id }}" data-type="blocks">{{ $otherTask->title }}</option>
                                    @endif
                                @endforeach
                            </optgroup>
                            <optgroup label="Related">
                                @foreach($project->tasks()->whereNull('deleted_at')->where('id', '!=', $task->id)->get() as $otherTask)
                                    @php
                                        $alreadyRelated = $task->dependencies()->where('type', 'related')->where('depends_on_task_id', $otherTask->id)->exists();
                                    @endphp
                                    @if(!$alreadyRelated)
                                        <option value="{{ $otherTask->id }}" data-type="related">{{ $otherTask->title }}</option>
                                    @endif
                                @endforeach
                            </optgroup>
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addDependencyBtn">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            @else
            <div class="col-md-6 mb-0"></div>
            @endif
        </div>
    </div>

    <!-- Checklist -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="fas fa-tasks"></i>Checklist
        </div>
        <div id="checklistItemsContainer">
            @if($checklistItems->count() > 0)
                @foreach($checklistItems->sortBy('sort_order') as $index => $item)
                    <div class="checklist-item" data-item-id="{{ $item->id }}" draggable="true">
                        <i class="fas fa-grip-vertical drag-handle"></i>
                        <input type="checkbox" class="form-check-input" name="checklist_items[{{ $item->id }}][is_completed]" value="1" {{ $item->is_completed ? 'checked' : '' }} draggable="false">
                        <input type="text" class="form-control form-control-sm" name="checklist_items[{{ $item->id }}][title]" value="{{ $item->title }}" placeholder="Checklist item" draggable="false">
                        <input type="hidden" name="checklist_items[{{ $item->id }}][id]" value="{{ $item->id }}">
                        <input type="hidden" name="checklist_items[{{ $item->id }}][sort_order]" value="{{ $item->sort_order ?? $index }}" class="sort-order-input">
                        <div class="checklist-item-actions">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-checklist-item" data-item-id="{{ $item->id }}" draggable="false">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <div class="mt-2">
            <button type="button" class="btn btn-sm btn-outline-primary" id="addChecklistItemBtn">
                <i class="fas fa-plus me-1"></i>Add Item
            </button>
        </div>
    </div>

    <!-- Custom Fields -->
    @if(isset($customFieldDefinitions) && $customFieldDefinitions && $customFieldDefinitions->count() > 0)
        <div class="form-section">
            <div class="form-section-title">
                <i class="fas fa-list-alt"></i>Custom Fields
            </div>
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

    <!-- Attachments -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="fas fa-paperclip"></i>Attachments
        </div>
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
                <div class="row g-2">
                    @foreach($task->attachments->where('type', 'attachment') as $attachment)
                        <div class="col-md-3">
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
        <div class="mb-0">
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
    </div>

    <!-- Form Actions -->
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fas {{ $submitButtonIcon }} me-2"></i>{{ $submitButtonText }}
        </button>
        <a href="{{ $cancelUrl }}" class="btn btn-outline-secondary">
            Cancel
        </a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Checklist Management
    let checklistItemCounter = {{ $checklistItems->count() }};
    const addChecklistItemBtn = document.getElementById('addChecklistItemBtn');
    const checklistContainer = document.getElementById('checklistItemsContainer');
    
    if (addChecklistItemBtn) {
        addChecklistItemBtn.addEventListener('click', function() {
            const newItem = document.createElement('div');
            newItem.className = 'checklist-item';
            newItem.draggable = true;
            newItem.setAttribute('data-new-item-index', checklistItemCounter);
            
            // Get current max sort_order from existing items
            const existingItems = checklistContainer.querySelectorAll('.checklist-item .sort-order-input');
            let maxSortOrder = -1;
            existingItems.forEach(input => {
                const value = parseInt(input.value) || 0;
                if (value > maxSortOrder) maxSortOrder = value;
            });
            
            newItem.innerHTML = `
                <i class="fas fa-grip-vertical drag-handle"></i>
                <input type="checkbox" class="form-check-input" disabled draggable="false">
                <input type="text" class="form-control form-control-sm" name="checklist_items_new[${checklistItemCounter}][title]" placeholder="Checklist item" required draggable="false">
                <input type="hidden" name="checklist_items_new[${checklistItemCounter}][sort_order]" value="${maxSortOrder + 1}" class="sort-order-input">
                <div class="checklist-item-actions">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-checklist-item-new" draggable="false">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            checklistContainer.appendChild(newItem);
            checklistItemCounter++;
            
            // Initialize drag-and-drop for the new item
            initializeChecklistItemDragAndDrop(newItem);
            
            // Update sort orders after adding
            updateChecklistSortOrders();
            
            // Focus on the new input
            newItem.querySelector('input[type="text"]').focus();
        });
    }
    
    // Shared variable for drag-and-drop (needs to be in outer scope)
    let draggedChecklistItem = null;
    
    // Initialize drag-and-drop for checklist items
    function initializeChecklistItemDragAndDrop(item) {
        item.addEventListener('dragstart', function(e) {
            // Don't start drag if clicking directly on inputs, checkboxes, or buttons
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'BUTTON' || e.target.closest('button')) {
                e.preventDefault();
                return false;
            }
            
            draggedChecklistItem = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            // Use a simple text placeholder since we're moving the actual element
            e.dataTransfer.setData('text/plain', '');
        });
        
        item.addEventListener('dragend', function(e) {
            this.classList.remove('dragging');
            // Remove drag-over class from all items
            checklistContainer.querySelectorAll('.checklist-item').forEach(el => {
                el.classList.remove('drag-over');
            });
            draggedChecklistItem = null;
            updateChecklistSortOrders();
        });
        
        item.addEventListener('dragover', function(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.dataTransfer.dropEffect = 'move';
            
            if (draggedChecklistItem && draggedChecklistItem !== this) {
                const afterElement = getDragAfterElement(checklistContainer, e.clientY);
                
                if (afterElement == null) {
                    checklistContainer.appendChild(draggedChecklistItem);
                } else {
                    checklistContainer.insertBefore(draggedChecklistItem, afterElement);
                }
            }
            
            return false;
        });
        
        item.addEventListener('dragenter', function(e) {
            if (draggedChecklistItem && draggedChecklistItem !== this) {
                this.classList.add('drag-over');
            }
        });
        
        item.addEventListener('dragleave', function(e) {
            this.classList.remove('drag-over');
        });
        
        item.addEventListener('drop', function(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }
            e.preventDefault();
            this.classList.remove('drag-over');
            return false;
        });
    }
    
    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.checklist-item:not(.dragging)')];
        
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
    
    // Update sort_order values based on DOM order
    function updateChecklistSortOrders() {
        const items = checklistContainer.querySelectorAll('.checklist-item');
        items.forEach((item, index) => {
            const sortOrderInput = item.querySelector('.sort-order-input');
            if (sortOrderInput) {
                sortOrderInput.value = index;
            }
        });
    }
    
    // Initialize drag-and-drop for existing checklist items and container
    if (checklistContainer) {
        // Initialize for existing items
        const existingItems = checklistContainer.querySelectorAll('.checklist-item');
        existingItems.forEach(item => {
            initializeChecklistItemDragAndDrop(item);
        });
        
        // Also handle dragover on the container itself (in case dropping between items)
        checklistContainer.addEventListener('dragover', function(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.dataTransfer.dropEffect = 'move';
            
            if (draggedChecklistItem) {
                const afterElement = getDragAfterElement(checklistContainer, e.clientY);
                
                if (afterElement == null) {
                    checklistContainer.appendChild(draggedChecklistItem);
                } else {
                    checklistContainer.insertBefore(draggedChecklistItem, afterElement);
                }
            }
            
            return false;
        });
        
        checklistContainer.addEventListener('drop', function(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            // Remove drag-over from all items
            checklistContainer.querySelectorAll('.checklist-item').forEach(el => {
                el.classList.remove('drag-over');
            });
            return false;
        });
    }
    
    // Remove checklist items (existing)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-checklist-item')) {
            const itemId = e.target.closest('.remove-checklist-item').dataset.itemId;
            if (confirm('Delete this checklist item?')) {
                    @if($isEdit && $task)
                fetch('{{ route("drives.projects.projects.tasks.checklist-items.destroy", [$drive, $project, $task, ":item"]) }}'.replace(':item', itemId), {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        e.target.closest('.checklist-item').remove();
                        updateChecklistSortOrders();
                    } else {
                        alert('Failed to delete checklist item');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete checklist item');
                });
                @else
                e.target.closest('.checklist-item').remove();
                updateChecklistSortOrders();
                @endif
            }
        }
        
        // Remove new checklist items (not yet saved)
        if (e.target.closest('.remove-checklist-item-new')) {
            e.target.closest('.checklist-item').remove();
            updateChecklistSortOrders();
        }
    });
    
    // Dependency Management (simplified)
    @if($isEdit && $task)
    const addDependencyBtn = document.getElementById('addDependencyBtn');
    const dependencySelect = document.getElementById('dependencySelect');
    
    if (addDependencyBtn && dependencySelect) {
        addDependencyBtn.addEventListener('click', function() {
            const selectedOption = dependencySelect.options[dependencySelect.selectedIndex];
            const taskId = dependencySelect.value;
            const type = selectedOption ? selectedOption.dataset.type : null;
            
            if (!taskId || !type) {
                alert('Please select a task and dependency type');
                return;
            }
            
            fetch('{{ route("drives.projects.projects.tasks.dependencies.store", [$drive, $project, $task]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    depends_on_task_id: parseInt(taskId),
                    type: type
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Failed to add dependency');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to add dependency');
            });
        });
    }
    
    // Remove dependency
    document.querySelectorAll('.remove-dependency').forEach(btn => {
        btn.addEventListener('click', function() {
            const dependencyId = this.dataset.dependencyId;
            if (!confirm('Remove this dependency?')) return;
            
            fetch('{{ route("drives.projects.projects.tasks.dependencies.destroy", [$drive, $project, $task, ":dependency"]) }}'.replace(':dependency', dependencyId), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to remove dependency');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to remove dependency');
            });
        });
    });
    @endif
    
    // Form submission handler - update sort orders before submitting
    const taskForm = document.getElementById('taskForm');
    if (taskForm) {
        taskForm.addEventListener('submit', function() {
            // Ensure sort orders are up to date before submitting
            if (typeof updateChecklistSortOrders === 'function') {
                updateChecklistSortOrders();
            }
        });
    }
});
</script>
