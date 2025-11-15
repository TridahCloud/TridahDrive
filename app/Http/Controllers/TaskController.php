<?php

namespace App\Http\Controllers;

use App\Events\TaskCreated;
use App\Events\TaskDeleted;
use App\Events\TaskMoved;
use App\Events\TaskUpdated;
use App\Models\Drive;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskChecklistItem;
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
        // Project-level permissions take priority - viewers cannot create
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'You do not have permission to create tasks.');
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
        // Project-level permissions take priority - viewers cannot create
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'You do not have permission to create tasks.');
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

        // Handle checklist items (new items)
        if ($request->has('checklist_items_new')) {
            // Sort new items by their sort_order before creating them
            $newItems = $request->input('checklist_items_new', []);
            usort($newItems, function($a, $b) {
                $sortA = isset($a['sort_order']) ? (int)$a['sort_order'] : 999;
                $sortB = isset($b['sort_order']) ? (int)$b['sort_order'] : 999;
                return $sortA <=> $sortB;
            });
            
            foreach ($newItems as $item) {
                if (!empty($item['title'])) {
                    // Use the sort_order from the form, or assign incrementally
                    $sortOrder = isset($item['sort_order']) ? (int)$item['sort_order'] : 0;
                    
                    $task->checklistItems()->create([
                        'title' => $item['title'],
                        'is_completed' => false,
                        'sort_order' => $sortOrder,
                        'created_by' => Auth::id(),
                    ]);
                }
            }
        }

        // Reload relationships for JSON response
        $task->load(['status', 'labels', 'members', 'owner', 'customFieldValues', 'checklistItems']);

        // Broadcast task created event (catch errors so they don't break the request)
        try {
            \Log::info('About to fire TaskCreated event', [
                'task_id' => $task->id,
                'project_id' => $task->project_id,
            ]);
            event(new TaskCreated($task));
            \Log::info('TaskCreated event fired', [
                'task_id' => $task->id,
                'project_id' => $task->project_id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast TaskCreated event', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the request if broadcasting fails
        }

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
            'parent.status',
            'checklistItems'
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
        // Project-level permissions take priority - viewers cannot edit
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'You do not have permission to edit tasks.');
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

        $task->load(['members', 'labels', 'attachments', 'status', 'customFieldValues.fieldDefinition', 'checklistItems']);

        return view('tasks.edit', compact('drive', 'project', 'task', 'driveMembers', 'labels', 'parentTasks', 'statuses', 'customFieldDefinitions'));
    }

    /**
     * Update the specified task
     */
    public function update(Request $request, Drive $drive, Project $project, Task $task)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        // Project-level permissions take priority - viewers cannot edit
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'You do not have permission to edit tasks.');
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

        // Handle checklist items
        if ($request->has('checklist_items')) {
            foreach ($request->input('checklist_items', []) as $item) {
                if (isset($item['id'])) {
                    $checklistItem = $task->checklistItems()->find($item['id']);
                    if ($checklistItem) {
                        $checklistItem->update([
                            'title' => $item['title'] ?? $checklistItem->title,
                            'is_completed' => isset($item['is_completed']) && $item['is_completed'] == '1',
                            'sort_order' => isset($item['sort_order']) ? (int)$item['sort_order'] : $checklistItem->sort_order,
                        ]);
                    }
                }
            }
        }
        
        // Handle new checklist items
        if ($request->has('checklist_items_new')) {
            // Get max sort_order from existing items (after updating existing items above)
            $maxSortOrder = $task->checklistItems()->max('sort_order') ?? -1;
            
            // Sort new items by their sort_order before creating them
            $newItems = $request->input('checklist_items_new', []);
            usort($newItems, function($a, $b) {
                $sortA = isset($a['sort_order']) ? (int)$a['sort_order'] : 999;
                $sortB = isset($b['sort_order']) ? (int)$b['sort_order'] : 999;
                return $sortA <=> $sortB;
            });
            
            foreach ($newItems as $item) {
                if (!empty($item['title'])) {
                    $sortOrder = isset($item['sort_order']) ? (int)$item['sort_order'] : (++$maxSortOrder);
                    // Ensure sort_order doesn't conflict with existing items
                    if ($sortOrder <= $maxSortOrder) {
                        $maxSortOrder = max($maxSortOrder, $sortOrder);
                    }
                    
                    $task->checklistItems()->create([
                        'title' => $item['title'],
                        'is_completed' => false,
                        'sort_order' => $sortOrder,
                        'created_by' => Auth::id(),
                    ]);
                    
                    $maxSortOrder = max($maxSortOrder, $sortOrder);
                }
            }
        }

        // Reload relationships for response
        $task->load(['status', 'labels', 'members', 'owner', 'customFieldValues', 'checklistItems']);

        // Broadcast task updated event (catch errors so they don't break the request)
        try {
            event(new TaskUpdated($task));
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast TaskUpdated event', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the request if broadcasting fails
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
        // Project-level permissions take priority - viewers cannot delete
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'You do not have permission to delete tasks.');
        }

        if ($project->drive_id !== $drive->id || $task->project_id !== $project->id) {
            abort(404);
        }

        $taskId = $task->id;
        $projectId = $task->project_id;

        $task->delete();

        // Broadcast task deleted event (catch errors so they don't break the request)
        try {
            event(new TaskDeleted($taskId, $projectId));
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast TaskDeleted event', [
                'task_id' => $taskId,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the request if broadcasting fails
        }

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
        // Project-level permissions take priority - viewers cannot modify
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'You do not have permission to modify tasks.');
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

        $oldStatusId = $task->task_status_id;
        $newStatusId = $validated['status_id'];
        $newSortOrder = $validated['sort_order'] ?? $task->sort_order;

        $task->update([
            'task_status_id' => $newStatusId,
            'sort_order' => $newSortOrder,
        ]);

        $task->load('status');

        // Broadcast task moved event (catch errors so they don't break the request)
        try {
            event(new TaskMoved($task, $oldStatusId, $newStatusId, $newSortOrder));
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast TaskMoved event', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the request if broadcasting fails
        }

        return response()->json(['success' => true, 'task' => $task]);
    }

    /**
     * Batch update task order in a column (for kanban drag and drop within same column)
     */
    public function batchUpdateOrder(Request $request, Drive $drive, Project $project)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to modify
        // Project-level permissions take priority - viewers cannot modify
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'You do not have permission to modify tasks.');
        }

        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status_id' => [
                'required',
                Rule::exists('task_statuses', 'id')->where('project_id', $project->id),
            ],
            'task_orders' => 'required|array',
            'task_orders.*.task_id' => 'required|exists:tasks,id',
            'task_orders.*.sort_order' => 'required|integer|min:0',
        ]);

        $statusId = $validated['status_id'];
        $taskOrders = $validated['task_orders'];

        try {
            // Get old status IDs before update
            $taskIds = collect($taskOrders)->pluck('task_id');
            $tasksBefore = Task::whereIn('id', $taskIds)
                ->where('project_id', $project->id)
                ->get()
                ->keyBy('id');

            \DB::transaction(function () use ($project, $statusId, $taskOrders) {
                foreach ($taskOrders as $order) {
                    $task = \DB::table('tasks')
                        ->where('id', $order['task_id'])
                        ->where('project_id', $project->id)
                        ->first();

                    if (!$task) {
                        continue;
                    }

                    // Update status if changed, and always update sort_order
                    $updateData = [
                        'sort_order' => $order['sort_order'],
                        'updated_at' => now(),
                    ];

                    // Only update status_id if it's different (to avoid unnecessary updates)
                    if ($task->task_status_id != $statusId) {
                        $updateData['task_status_id'] = $statusId;
                    }

                    \DB::table('tasks')
                        ->where('id', $order['task_id'])
                        ->where('project_id', $project->id)
                        ->update($updateData);
                }
            });

            // Load updated tasks with their status
            $tasks = Task::whereIn('id', $taskIds)
                ->where('project_id', $project->id)
                ->with('status')
                ->get()
                ->keyBy('id');

            // Broadcast task moved events for any tasks that changed status
            foreach ($taskOrders as $order) {
                $task = $tasks->get($order['task_id']);
                $taskBefore = $tasksBefore->get($order['task_id']);
                if ($task && $taskBefore) {
                    try {
                        event(new TaskMoved($task, $taskBefore->task_status_id, $statusId, $order['sort_order']));
                    } catch (\Exception $e) {
                        \Log::error('Failed to broadcast TaskMoved event', [
                            'task_id' => $task->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Task order updated successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('Batch update task order failed', [
                'project_id' => $project->id,
                'status_id' => $statusId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update task order',
            ], 500);
        }
    }

    /**
     * Duplicate a task
     */
    public function duplicate(Request $request, Drive $drive, Project $project, Task $task)
    {
        $this->authorize('view', $drive);
        
        // Project-level permissions take priority - viewers cannot modify
        if (!$project->userCanEdit(auth()->user())) {
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
        
        // Project-level permissions take priority - viewers cannot modify
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'Viewers cannot archive tasks.');
        }

        if ($project->drive_id !== $drive->id || $task->project_id !== $project->id) {
            abort(404);
        }

        // Soft delete the task (use delete() method for SoftDeletes)
        $task->delete();

        // Broadcast task deleted event so other browsers remove it
        try {
            event(new TaskDeleted($task->id, $project->id));
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast TaskDeleted event', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the request if broadcasting fails
        }

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
        
        // Project-level permissions take priority - viewers cannot modify
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'Viewers cannot unarchive tasks.');
        }

        if ($project->drive_id !== $drive->id || $task->project_id !== $project->id) {
            abort(404);
        }

        // Restore the soft-deleted task
        $task->restore();

        // Reload task relationships for broadcasting
        $task->load([
            'status:id,name,slug,color',
            'owner:id,name,email',
            'creator:id,name,email',
            'members:id,name,email',
            'labels:id,name,color',
            'customFieldValues.fieldDefinition:id,name,type',
        ]);

        // Broadcast task created event so other browsers add it back
        try {
            event(new \App\Events\TaskCreated($task));
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast TaskCreated event', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the request if broadcasting fails
        }

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
        // Project-level permissions take priority - viewers cannot modify
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'You do not have permission to modify tasks.');
        }

        if ($project->drive_id !== $drive->id || $task->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'label_ids' => 'nullable|array',
            'label_ids.*' => 'exists:task_labels,id',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        // Update title
        if (isset($validated['title']) && $validated['title']) {
            $task->title = $validated['title'];
        }

        // Update priority
        if (isset($validated['priority'])) {
            $task->priority = $validated['priority'];
        }

        // Update description
        if (isset($validated['description'])) {
            $task->description = $this->sanitizeDescription($validated['description']);
        }

        // Update due_date - explicitly handle null to allow removal
        if (array_key_exists('due_date', $validated)) {
            $task->due_date = $validated['due_date'] ? \Carbon\Carbon::parse($validated['due_date']) : null;
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
        $task->load(['labels', 'members', 'status']);

        // Broadcast task updated event (catch errors so they don't break the request)
        try {
            event(new TaskUpdated($task));
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast TaskUpdated event', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the request if broadcasting fails
        }

        return response()->json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'priority' => $task->priority,
                'description' => $task->description,
                'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                'is_overdue' => (bool) $task->isOverdue(),
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

        // Project-level permissions take priority - viewers cannot delete attachments
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'You do not have permission to delete attachments.');
        }

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
        
        // Project-level permissions take priority - viewers cannot modify
        if (!$project->userCanEdit(auth()->user())) {
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
        
        // Project-level permissions take priority - viewers cannot modify
        if (!$project->userCanEdit(auth()->user())) {
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

    /**
     * Store a checklist item for a task
     */
    public function storeChecklistItem(Request $request, Drive $drive, Project $project, Task $task)
    {
        $this->authorize('view', $drive);
        
        // Project-level permissions take priority - viewers cannot modify
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'Viewers cannot modify tasks.');
        }

        if ($project->drive_id !== $drive->id || $task->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        // Get max sort_order for this task
        $maxSortOrder = $task->checklistItems()->max('sort_order') ?? -1;

        $checklistItem = $task->checklistItems()->create([
            'title' => $validated['title'],
            'sort_order' => $validated['sort_order'] ?? ($maxSortOrder + 1),
            'created_by' => Auth::id(),
        ]);

        $checklistItem->load('creator');
        $progress = $task->fresh()->checklist_progress;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'checklist_item' => $checklistItem,
                'progress' => $progress,
            ]);
        }

        return redirect()->back()->with('success', 'Checklist item added successfully!');
    }

    /**
     * Update a checklist item
     */
    public function updateChecklistItem(Request $request, Drive $drive, Project $project, Task $task, TaskChecklistItem $checklistItem)
    {
        $this->authorize('view', $drive);
        
        // Project-level permissions take priority - viewers cannot modify
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'Viewers cannot modify tasks.');
        }

        if ($project->drive_id !== $drive->id || 
            $task->project_id !== $project->id || 
            $checklistItem->task_id !== $task->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'is_completed' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        $checklistItem->update($validated);
        $progress = $task->fresh()->checklist_progress;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'checklist_item' => $checklistItem->fresh(),
                'progress' => $progress,
            ]);
        }

        return redirect()->back()->with('success', 'Checklist item updated successfully!');
    }

    /**
     * Toggle checklist item completion
     */
    public function toggleChecklistItem(Request $request, Drive $drive, Project $project, Task $task, TaskChecklistItem $checklistItem)
    {
        $this->authorize('view', $drive);
        
        // Project-level permissions take priority - viewers cannot modify
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'Viewers cannot modify tasks.');
        }

        if ($project->drive_id !== $drive->id || 
            $task->project_id !== $project->id || 
            $checklistItem->task_id !== $task->id) {
            abort(404);
        }

        $checklistItem->update([
            'is_completed' => !$checklistItem->is_completed,
        ]);

        $progress = $task->fresh()->checklist_progress;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'checklist_item' => $checklistItem->fresh(),
                'progress' => $progress,
            ]);
        }

        return redirect()->back()->with('success', 'Checklist item updated successfully!');
    }

    /**
     * Delete a checklist item
     */
    public function destroyChecklistItem(Drive $drive, Project $project, Task $task, TaskChecklistItem $checklistItem)
    {
        $this->authorize('view', $drive);
        
        // Project-level permissions take priority - viewers cannot modify
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'Viewers cannot modify tasks.');
        }

        if ($project->drive_id !== $drive->id || 
            $task->project_id !== $project->id || 
            $checklistItem->task_id !== $task->id) {
            abort(404);
        }

        $checklistItem->delete();
        $progress = $task->fresh()->checklist_progress;

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Checklist item deleted successfully',
                'progress' => $progress,
            ]);
        }

        return redirect()->back()->with('success', 'Checklist item deleted successfully!');
    }

    /**
     * Reorder checklist items for a task
     */
    public function reorderChecklistItems(Request $request, Drive $drive, Project $project, Task $task)
    {
        $this->authorize('view', $drive);
        
        // Project-level permissions take priority - viewers cannot modify
        if (!$project->userCanEdit(auth()->user())) {
            abort(403, 'Viewers cannot modify tasks.');
        }

        if ($project->drive_id !== $drive->id || $task->project_id !== $project->id) {
            abort(404);
        }

        // Log the request - this should always fire if the route is hit
        \Log::info('=== REORDER CHECKLIST ITEMS REQUEST RECEIVED ===', [
            'task_id' => $task->id,
            'project_id' => $project->id,
            'drive_id' => $drive->id,
            'request_data' => $request->all(),
            'item_ids' => $request->input('item_ids'),
            'user_id' => auth()->id(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
        ]);

        // Validate input
        try {
            $validated = $request->validate([
                'item_ids' => 'required|array',
                'item_ids.*' => 'exists:task_checklist_items,id',
            ]);
            \Log::info('Validation passed', ['validated' => $validated]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            throw $e;
        }

        // Get all checklist items for this task
        $taskItemIds = $task->checklistItems()->pluck('id')->toArray();
        $requestedItemIds = $validated['item_ids'];
        
        // Verify all requested items belong to this task
        $invalidItems = array_diff($requestedItemIds, $taskItemIds);
        if (!empty($invalidItems)) {
            \Log::warning('Invalid checklist items in reorder request', [
                'task_id' => $task->id,
                'requested_ids' => $requestedItemIds,
                'task_item_ids' => $taskItemIds,
                'invalid_ids' => $invalidItems,
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Some checklist items do not belong to this task',
            ], 400);
        }

        // Verify we have all items (count should match)
        if (count($requestedItemIds) !== count($taskItemIds)) {
            \Log::warning('Item count mismatch in reorder request', [
                'task_id' => $task->id,
                'requested_count' => count($requestedItemIds),
                'task_item_count' => count($taskItemIds),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Item count mismatch. All checklist items must be included in reorder.',
            ], 400);
        }

        // Update sort_order for each item based on the order in the array
        // Use a transaction to ensure all updates happen atomically
        try {
            $updateResults = [];
            \DB::transaction(function () use ($task, $validated, &$updateResults) {
                foreach ($validated['item_ids'] as $index => $itemId) {
                    // First, check current state
                    $currentItem = \DB::table('task_checklist_items')
                        ->where('id', $itemId)
                        ->where('task_id', $task->id)
                        ->first(['id', 'sort_order', 'title']);
                    
                    if (!$currentItem) {
                        \Log::warning('Item not found for update', [
                            'item_id' => $itemId,
                            'task_id' => $task->id,
                        ]);
                        $updateResults[] = ['item_id' => $itemId, 'status' => 'not_found'];
                        continue;
                    }
                    
                    // Only update if sort_order is different
                    if ($currentItem->sort_order != $index) {
                        // Use direct database update for reliability
                        $updated = \DB::table('task_checklist_items')
                            ->where('id', $itemId)
                            ->where('task_id', $task->id)
                            ->update([
                                'sort_order' => $index,
                                'updated_at' => now(),
                            ]);
                        
                        $updateResults[] = [
                            'item_id' => $itemId,
                            'old_sort_order' => $currentItem->sort_order,
                            'new_sort_order' => $index,
                            'updated' => $updated > 0,
                            'rows_affected' => $updated,
                        ];
                        
                        \Log::info('Updated checklist item sort_order via DB', [
                            'item_id' => $itemId,
                            'task_id' => $task->id,
                            'old_sort_order' => $currentItem->sort_order,
                            'new_sort_order' => $index,
                            'rows_affected' => $updated,
                        ]);
                    } else {
                        $updateResults[] = [
                            'item_id' => $itemId,
                            'status' => 'no_change',
                            'sort_order' => $index,
                        ];
                    }
                }
            });
            
            \Log::info('Update transaction completed', [
                'task_id' => $task->id,
                'results' => $updateResults,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating checklist item order', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to update checklist item order: ' . $e->getMessage(),
            ], 500);
        }

        // Clear any query cache and reload task and checklist items with updated order
        $task->unsetRelation('checklistItems');
        
        // Verify the updates were saved to database
        $dbItems = \DB::table('task_checklist_items')
            ->where('task_id', $task->id)
            ->orderBy('sort_order')
            ->get(['id', 'sort_order', 'title']);
        
        \Log::info('Database state after reorder', [
            'task_id' => $task->id,
            'db_items' => $dbItems->map(function($item) {
                return ['id' => $item->id, 'sort_order' => $item->sort_order, 'title' => $item->title];
            })->toArray(),
        ]);
        
        // Reload task with fresh data
        $task = $task->fresh(['checklistItems']);
        $progress = $task->checklist_progress;
        
        // Log the updated order for debugging
        \Log::info('Checklist items reordered (from model)', [
            'task_id' => $task->id,
            'items' => $task->checklistItems->map(function($item) {
                return ['id' => $item->id, 'sort_order' => $item->sort_order, 'title' => $item->title];
            })->toArray(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            // Ensure items are sorted by sort_order (relationship should already do this, but be explicit)
            $checklistItems = $task->checklistItems->sortBy('sort_order')->values();
            
            $responseData = [
                'success' => true,
                'message' => 'Checklist items reordered successfully',
                'checklist_items' => $checklistItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'is_completed' => $item->is_completed,
                        'sort_order' => (int) $item->sort_order,
                    ];
                })->toArray(),
                'progress' => $progress,
            ];
            
            \Log::info('Sending reorder response', [
                'task_id' => $task->id,
                'item_count' => count($responseData['checklist_items']),
                'items' => $responseData['checklist_items'],
            ]);
            
            return response()->json($responseData);
        }

        return redirect()->back()->with('success', 'Checklist items reordered successfully!');
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
