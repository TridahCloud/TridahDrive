<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskDependency;
use App\Models\TaskLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks for a project
     */
    public function index(Drive $drive, Project $project)
    {
        $this->authorize('view', $drive);

        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        $tasks = $project->tasks()
            ->with(['owner', 'creator', 'members', 'labels', 'attachments'])
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tasks.index', compact('drive', 'project', 'tasks'));
    }

    /**
     * Show the form for creating a new task
     */
    public function create(Drive $drive, Project $project)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create tasks.');
        }

        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        $driveMembers = $drive->users()->get();
        $labels = $drive->taskLabels()->where('is_active', true)->get();
        $parentTasks = $project->tasks()->whereNull('parent_id')->whereNull('deleted_at')->get();
        $statuses = $project->taskStatuses()->get();
        $customFieldDefinitions = $project->customFieldDefinitions()->where('is_active', true)->orderBy('sort_order')->get();

        return view('tasks.create', compact('drive', 'project', 'driveMembers', 'labels', 'parentTasks', 'statuses', 'customFieldDefinitions'));
    }

    /**
     * Store a newly created task
     */
    public function store(Request $request, Drive $drive, Project $project)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create tasks.');
        }

        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => [
                'required',
                Rule::exists('task_statuses', 'id')->where('project_id', $project->id),
            ],
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer|min:0',
            'owner_id' => 'nullable|exists:users,id',
            'parent_id' => 'nullable|exists:tasks,id',
            'label_ids' => 'nullable|array',
            'label_ids.*' => 'exists:task_labels,id',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB per file
            'header_image' => 'nullable|image|max:10240', // For kanban card header
            'custom_fields' => 'nullable|array',
        ]);

        // Convert dates from user timezone to drive timezone
        $driveTimezone = $drive->getEffectiveTimezone();
        $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
        
        if (isset($validated['due_date'])) {
            $dueDate = \Carbon\Carbon::parse($validated['due_date'], $userTimezone);
            $dueDate->setTimezone($driveTimezone);
            $validated['due_date'] = $dueDate->format('Y-m-d');
        }
        
        if (isset($validated['start_date'])) {
            $startDate = \Carbon\Carbon::parse($validated['start_date'], $userTimezone);
            $startDate->setTimezone($driveTimezone);
            $validated['start_date'] = $startDate->format('Y-m-d');
        }

        $validated['description'] = $this->sanitizeDescription($validated['description'] ?? null);

        // Get max sort_order for this status
        $maxSortOrder = $project->tasks()
            ->where('task_status_id', $validated['status_id'])
            ->whereNull('deleted_at')
            ->max('sort_order') ?? -1;

        $task = $project->tasks()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'task_status_id' => $validated['status_id'],
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'owner_id' => $validated['owner_id'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'sort_order' => $maxSortOrder + 1,
            'created_by' => Auth::id(),
        ]);

        // Attach labels
        if (!empty($validated['label_ids'])) {
            $task->labels()->attach($validated['label_ids']);
        }

        // Attach members
        if (!empty($validated['member_ids'])) {
            foreach ($validated['member_ids'] as $userId) {
                $task->taskMembers()->create([
                    'user_id' => $userId,
                    'assigned_by' => Auth::id(),
                ]);
            }
        }

        // Handle header image upload
        if ($request->hasFile('header_image')) {
            $this->storeHeaderImage($task, $request->file('header_image'));
        }

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $this->storeAttachment($task, $file, 'attachment');
            }
        }

        // Handle custom fields
        if ($request->has('custom_fields')) {
            $this->syncCustomFields($task, $request->input('custom_fields', []));
        }

        // Reload relationships for JSON response
        $task->load(['status', 'labels', 'members', 'owner', 'customFieldValues']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'task' => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description ?? '',
                    'priority' => $task->priority,
                    'status' => $task->status ? [
                        'id' => $task->status->id,
                        'slug' => $task->status->slug,
                        'name' => $task->status->name,
                        'color' => $task->status->color,
                    ] : null,
                    'labels' => $task->labels->map(function ($label) {
                        return [
                            'id' => $label->id,
                            'name' => $label->name,
                            'color' => $label->color,
                        ];
                    })->toArray(),
                    'members' => $task->members->map(function ($member) {
                        return [
                            'id' => $member->id,
                            'name' => $member->name,
                        ];
                    })->toArray(),
                    'owner' => $task->owner ? $task->owner->name : null,
                    'owner_id' => $task->owner_id,
                    'url' => route('drives.projects.projects.tasks.show', [$drive, $project, $task]),
                    'edit_url' => route('drives.projects.projects.tasks.edit', [$drive, $project, $task]),
                ],
            ]);
        }

        return redirect()->route('drives.projects.projects.show', [$drive, $project, 'view' => 'list'])
            ->with('success', 'Task created successfully!');
    }

    /**
     * Display the specified task
     */
    public function show(Drive $drive, Project $project, Task $task)
    {
        $this->authorize('view', $drive);

        if ($project->drive_id !== $drive->id || $task->project_id !== $project->id) {
            abort(404);
        }

        $task->load([
            'owner', 
            'creator', 
            'members', 
            'labels', 
            'attachments', 
            'subtasks.status',
            'comments.user',
            'comments.replies.user',
            'status',
            'customFieldValues.fieldDefinition',
            'dependencies.dependsOnTask.status',
            'parent.status'
        ]);
        $driveMembers = $drive->users()->get();
        $labels = $drive->taskLabels()->where('is_active', true)->get();
        $customFieldDefinitions = $project->customFieldDefinitions()->where('is_active', true)->orderBy('sort_order')->get();

        return view('tasks.show', compact('drive', 'project', 'task', 'driveMembers', 'labels', 'customFieldDefinitions'));
    }

    /**
     * Show the form for editing the specified task
     */
    public function edit(Drive $drive, Project $project, Task $task)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit tasks.');
        }

        if ($project->drive_id !== $drive->id || $task->project_id !== $project->id) {
            abort(404);
        }

        $driveMembers = $drive->users()->get();
        $labels = $drive->taskLabels()->where('is_active', true)->get();
        $parentTasks = $project->tasks()
            ->whereNull('parent_id')
            ->where('id', '!=', $task->id)
            ->whereNull('deleted_at')
            ->get();
        $statuses = $project->taskStatuses()->get();
        $customFieldDefinitions = $project->customFieldDefinitions()->where('is_active', true)->orderBy('sort_order')->get();

        $task->load(['members', 'labels', 'attachments', 'status', 'customFieldValues.fieldDefinition']);

        return view('tasks.edit', compact('drive', 'project', 'task', 'driveMembers', 'labels', 'parentTasks', 'statuses', 'customFieldDefinitions'));
    }

    /**
     * Update the specified task
     */
    public function update(Request $request, Drive $drive, Project $project, Task $task)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit tasks.');
        }

        if ($project->drive_id !== $drive->id || $task->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => [
                'required',
                Rule::exists('task_statuses', 'id')->where('project_id', $project->id),
            ],
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer|min:0',
            'actual_hours' => 'nullable|integer|min:0',
            'owner_id' => 'nullable|exists:users,id',
            'parent_id' => 'nullable|exists:tasks,id',
            'label_ids' => 'nullable|array',
            'label_ids.*' => 'exists:task_labels,id',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
            'header_image' => 'nullable|image|max:10240',
            'custom_fields' => 'nullable|array',
        ]);

        // Convert dates from user timezone to drive timezone
        $driveTimezone = $drive->getEffectiveTimezone();
        $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
        
        if (isset($validated['due_date'])) {
            $dueDate = \Carbon\Carbon::parse($validated['due_date'], $userTimezone);
            $dueDate->setTimezone($driveTimezone);
            $validated['due_date'] = $dueDate->format('Y-m-d');
        }
        
        if (isset($validated['start_date'])) {
            $startDate = \Carbon\Carbon::parse($validated['start_date'], $userTimezone);
            $startDate->setTimezone($driveTimezone);
            $validated['start_date'] = $startDate->format('Y-m-d');
        }

        $validated['description'] = $this->sanitizeDescription($validated['description'] ?? null);

        // Update task
        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'task_status_id' => $validated['status_id'],
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'actual_hours' => $validated['actual_hours'] ?? null,
            'owner_id' => $validated['owner_id'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        // Sync labels
        if (isset($validated['label_ids'])) {
            $task->labels()->sync($validated['label_ids']);
        }

        // Sync members
        if (isset($validated['member_ids'])) {
            $task->taskMembers()->delete();
            foreach ($validated['member_ids'] as $userId) {
                $task->taskMembers()->create([
                    'user_id' => $userId,
                    'assigned_by' => Auth::id(),
                ]);
            }
        }

        // Handle header image upload
        if ($request->hasFile('header_image')) {
            // Delete old header images
            $task->attachments()->where('type', 'header')->delete();
            $this->storeHeaderImage($task, $request->file('header_image'));
        }

        // Handle new attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $this->storeAttachment($task, $file, 'attachment');
            }
        }

        // Handle custom fields
        if ($request->has('custom_fields')) {
            $this->syncCustomFields($task, $request->input('custom_fields', []));
        }

        return redirect()->route('drives.projects.projects.tasks.show', [$drive, $project, $task])
            ->with('success', 'Task updated successfully!');
    }

    /**
     * Remove the specified task
     */
    public function destroy(Drive $drive, Project $project, Task $task)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to delete
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot delete tasks.');
        }

        if ($project->drive_id !== $drive->id || $task->project_id !== $project->id) {
            abort(404);
        }

        $task->delete();

        return redirect()->route('drives.projects.projects.show', [$drive, $project])
            ->with('success', 'Task deleted successfully!');
    }

    /**
     * Update task status (for kanban drag and drop)
     */
    public function updateStatus(Request $request, Drive $drive, Project $project, Task $task)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to modify
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot modify tasks.');
        }

        if ($project->drive_id !== $drive->id || $task->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status_id' => [
                'required',
                Rule::exists('task_statuses', 'id')->where('project_id', $project->id),
            ],
            'sort_order' => 'nullable|integer',
        ]);

        $task->update([
            'task_status_id' => $validated['status_id'],
            'sort_order' => $validated['sort_order'] ?? $task->sort_order,
        ]);

        $task->load('status');

        return response()->json(['success' => true, 'task' => $task]);
    }

    /**
     * Duplicate a task
     */
    public function duplicate(Request $request, Drive $drive, Project $project, Task $task)
    {
        $this->authorize('view', $drive);
        
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot duplicate tasks.');
        }

        if ($project->drive_id !== $drive->id || $task->project_id !== $project->id) {
            abort(404);
        }

        // Get max sort_order for the same status
        $maxSortOrder = $project->tasks()
            ->where('task_status_id', $task->task_status_id)
            ->whereNull('deleted_at')
            ->max('sort_order') ?? -1;

        $newTask = $task->replicate();
        $newTask->title = $task->title . ' (Copy)';
        $newTask->sort_order = $maxSortOrder + 1;
        $newTask->created_by = Auth::id();
        $newTask->save();

        // Copy labels
        $newTask->labels()->sync($task->labels->pluck('id'));

        // Copy members
        foreach ($task->members as $member) {
            $newTask->taskMembers()->create([
                'user_id' => $member->id,
                'assigned_by' => Auth::id(),
            ]);
        }

        // Reload relationships for response
        $newTask->load(['status', 'labels', 'members', 'owner', 'comments']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task duplicated successfully',
                'task' => [
                    'id' => $newTask->id,
                    'title' => $newTask->title,
                    'description' => $newTask->description ?? '',
                    'priority' => $newTask->priority,
                    'status' => $newTask->status ? [
                        'id' => $newTask->status->id,
                        'slug' => $newTask->status->slug,
                        'name' => $newTask->status->name,
                        'color' => $newTask->status->color,
                    ] : null,
                    'labels' => $newTask->labels->map(function ($label) {
                        return [
                            'id' => $label->id,
                            'name' => $label->name,
                            'color' => $label->color,
                        ];
                    })->toArray(),
                    'members' => $newTask->members->map(function ($member) {
                        return [
                            'id' => $member->id,
                            'name' => $member->name,
                        ];
                    })->toArray(),
                    'owner' => $newTask->owner ? $newTask->owner->name : null,
                    'owner_id' => $newTask->owner_id,
                    'url' => route('drives.projects.projects.tasks.show', [$drive, $project, $newTask]),
                    'edit_url' => route('drives.projects.projects.tasks.edit', [$drive, $project, $newTask]),
                ],
            ]);
        }

        return redirect()->route('drives.projects.projects.show', [$drive, $project])
            ->with('success', 'Task duplicated successfully!');
    }

    /**
     * Archive a task
     */
    public function archive(Request $request, Drive $drive, Project $project, Task $task)
    {
        $this->authorize('view', $drive);
        
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot archive tasks.');
        }

        if ($project->drive_id !== $drive->id || $task->project_id !== $project->id) {
            abort(404);
        }

        // Soft delete the task
        $task->update(['deleted_at' => now()]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Task archived successfully']);
        }

        return redirect()->back()->with('success', 'Task archived successfully!');
    }

    /**
     * Unarchive a task
     */
    public function unarchive(Request $request, Drive $drive, Project $project, Task $task)
    {
        $this->authorize('view', $drive);
        
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot unarchive tasks.');
        }

        if ($project->drive_id !== $drive->id || $task->project_id !== $project->id) {
            abort(404);
        }

        $task->update(['deleted_at' => null]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Task unarchived successfully']);
        }

        return redirect()->back()->with('success', 'Task unarchived successfully!');
    }

    /**
     * Update task labels and members (for kanban sidebar inline editing)
     */
    public function updateLabelsAndMembers(Request $request, Drive $drive, Project $project, Task $task)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to modify
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot modify tasks.');
        }

        if ($project->drive_id !== $drive->id || $task->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'label_ids' => 'nullable|array',
            'label_ids.*' => 'exists:task_labels,id',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'description' => 'nullable|string',
        ]);

        // Update priority
        if (isset($validated['priority'])) {
            $task->priority = $validated['priority'];
        }

        // Update description
        if (isset($validated['description'])) {
            $task->description = $this->sanitizeDescription($validated['description']);
        }

        // Sync labels
        if (isset($validated['label_ids'])) {
            $task->labels()->sync($validated['label_ids']);
        }

        // Sync members
        if (isset($validated['member_ids'])) {
            $task->taskMembers()->delete();
            foreach ($validated['member_ids'] as $userId) {
                $task->taskMembers()->create([
                    'user_id' => $userId,
                    'assigned_by' => Auth::id(),
                ]);
            }
        }

        $task->save();

        // Reload relationships
        $task->load(['labels', 'members']);

        return response()->json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'priority' => $task->priority,
                'description' => $task->description,
                'labels' => $task->labels->map(function ($label) {
                    return [
                        'id' => $label->id,
                        'name' => $label->name,
                        'color' => $label->color,
                    ];
                })->toArray(),
                'members' => $task->members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                    ];
                })->toArray(),
            ],
        ]);
    }

    /**
     * Store header image for task
     */
    protected function storeHeaderImage(Task $task, $file)
    {
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = 'task-headers/' . $task->project->drive_id . '/' . $task->project_id . '/' . $task->id;
        
        $filePath = $file->storeAs($path, $filename, 'public');

        TaskAttachment::create([
            'task_id' => $task->id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'type' => 'header',
            'uploaded_by' => Auth::id(),
        ]);
    }

    /**
     * Store attachment for task
     */
    protected function storeAttachment(Task $task, $file, string $type = 'attachment')
    {
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file->getSize() > $maxSize) {
            throw new \Exception('File size exceeds the maximum allowed size of 10MB.');
        }

        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = 'task-attachments/' . $task->project->drive_id . '/' . $task->project_id . '/' . $task->id;
        
        $filePath = $file->storeAs($path, $filename, 'public');

        TaskAttachment::create([
            'task_id' => $task->id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'type' => $type,
            'uploaded_by' => Auth::id(),
        ]);
    }

    /**
     * Delete a task attachment
     */
    public function destroyAttachment(Drive $drive, Project $project, Task $task, TaskAttachment $attachment)
    {
        $this->authorize('view', $drive);

        if ($project->drive_id !== $drive->id || 
            $task->project_id !== $project->id || 
            $attachment->task_id !== $task->id) {
            abort(404);
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return redirect()->back()
            ->with('success', 'Attachment deleted successfully!');
    }

    /**
     * Serve/download a task attachment
     */
    public function showAttachment(Drive $drive, Project $project, Task $task, TaskAttachment $attachment)
    {
        $this->authorize('view', $drive);

        if ($project->drive_id !== $drive->id || 
            $task->project_id !== $project->id || 
            $attachment->task_id !== $task->id) {
            abort(404);
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->response($attachment->file_path, $attachment->original_filename);
    }

    /**
     * Add a task dependency
     */
    public function addDependency(Request $request, Drive $drive, Project $project, Task $task)
    {
        $this->authorize('view', $drive);
        
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot modify task dependencies.');
        }

        if ($project->drive_id !== $drive->id || $task->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'depends_on_task_id' => 'required|exists:tasks,id',
            'type' => 'required|in:blocks,blocked_by,related',
            'notes' => 'nullable|string|max:500',
        ]);

        // Prevent self-dependencies
        if ($validated['depends_on_task_id'] == $task->id) {
            return response()->json(['error' => 'A task cannot depend on itself'], 400);
        }

        // Check if dependency already exists
        $existing = TaskDependency::where('task_id', $task->id)
            ->where('depends_on_task_id', $validated['depends_on_task_id'])
            ->where('type', $validated['type'])
            ->first();

        if ($existing) {
            return response()->json(['error' => 'This dependency already exists'], 400);
        }

        TaskDependency::create([
            'task_id' => $task->id,
            'depends_on_task_id' => $validated['depends_on_task_id'],
            'type' => $validated['type'],
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Dependency added successfully']);
        }

        return redirect()->back()->with('success', 'Dependency added successfully!');
    }

    /**
     * Remove a task dependency
     */
    public function removeDependency(Drive $drive, Project $project, Task $task, TaskDependency $dependency)
    {
        $this->authorize('view', $drive);
        
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot modify task dependencies.');
        }

        if ($project->drive_id !== $drive->id || 
            $task->project_id !== $project->id || 
            $dependency->task_id !== $task->id) {
            abort(404);
        }

        $dependency->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Dependency removed successfully']);
        }

        return redirect()->back()->with('success', 'Dependency removed successfully!');
    }

    /**
     * Sync custom field values for a task
     */
    private function syncCustomFields(Task $task, array $customFields)
    {
        // Get all custom field definitions for this project
        $fieldDefinitions = $task->project->customFieldDefinitions()->where('is_active', true)->get();
        
        foreach ($fieldDefinitions as $fieldDef) {
            $fieldId = $fieldDef->id;
            $value = $customFields[$fieldId] ?? null;
            
            // Handle checkbox (value can be "1" or true)
            if ($fieldDef->type === 'checkbox') {
                $value = ($value == '1' || $value === true || $value === 'true') ? '1' : '0';
            }
            
            // If value is empty and field is not required, delete existing value
            if (empty($value) && !$fieldDef->required) {
                $task->customFieldValues()->where('field_definition_id', $fieldId)->delete();
                continue;
            }
            
            // If value is empty and field is required, skip (validation should catch this)
            if (empty($value) && $fieldDef->required) {
                continue;
            }
            
            // Convert value to string for storage
            if (is_array($value)) {
                $value = json_encode($value);
            } elseif ($value !== null) {
                $value = (string) $value;
            } else {
                continue; // Skip null values
            }
            
            // Update or create the custom field value
            $task->customFieldValues()->updateOrCreate(
                ['field_definition_id' => $fieldId],
                ['value' => $value]
            );
        }
    }

    private function sanitizeDescription(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = strip_tags($value, '<p><br><strong><em><u><ol><ul><li><a><blockquote><code><pre><span><div><h1><h2><h3><h4><h5><h6>');
        $clean = trim($clean);

        return $clean === '' ? null : $clean;
    }
}
