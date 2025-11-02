<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        return view('tasks.create', compact('drive', 'project', 'driveMembers', 'labels', 'parentTasks'));
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
            'status' => 'required|in:todo,in_progress,review,done,blocked',
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
        ]);

        // Get max sort_order for this status
        $maxSortOrder = $project->tasks()
            ->where('status', $validated['status'])
            ->whereNull('deleted_at')
            ->max('sort_order') ?? -1;

        $task = $project->tasks()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
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
            'subtasks',
            'comments.user',
            'comments.replies.user'
        ]);
        $driveMembers = $drive->users()->get();
        $labels = $drive->taskLabels()->where('is_active', true)->get();

        return view('tasks.show', compact('drive', 'project', 'task', 'driveMembers', 'labels'));
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

        $task->load(['members', 'labels', 'attachments']);

        return view('tasks.edit', compact('drive', 'project', 'task', 'driveMembers', 'labels', 'parentTasks'));
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
            'status' => 'required|in:todo,in_progress,review,done,blocked',
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
        ]);

        // Update task
        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
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
            'status' => 'required|in:todo,in_progress,review,done,blocked',
            'sort_order' => 'nullable|integer',
        ]);

        $task->update([
            'status' => $validated['status'],
            'sort_order' => $validated['sort_order'] ?? $task->sort_order,
        ]);

        return response()->json(['success' => true, 'task' => $task]);
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
}
