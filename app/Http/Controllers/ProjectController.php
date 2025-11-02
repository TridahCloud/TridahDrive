<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects for the drive
     */
    public function index(Drive $drive)
    {
        $this->authorize('view', $drive);

        $projects = $drive->projects()
            ->with(['creator', 'tasks' => function($query) {
                $query->whereNull('deleted_at');
            }])
            ->withCount(['tasks as total_tasks', 'tasks as completed_tasks' => function($query) {
                $query->where('status', 'done')->whereNull('deleted_at');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('projects.index', compact('drive', 'projects'));
    }

    /**
     * Show the form for creating a new project
     */
    public function create(Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create projects.');
        }
        
        return view('projects.create', compact('drive'));
    }

    /**
     * Store a newly created project
     */
    public function store(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create projects.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_public' => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'header_image' => 'nullable|image|max:10240', // 10MB
        ]);

        $project = $drive->projects()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#3B82F6',
            'is_public' => $request->has('is_public'),
            'public_key' => $request->has('is_public') ? Project::generatePublicKey() : null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'created_by' => Auth::id(),
            'status' => 'active',
        ]);

        // Handle header image upload
        if ($request->hasFile('header_image')) {
            $this->storeHeaderImage($project, $request->file('header_image'));
        }

        return redirect()->route('drives.projects.projects.show', [$drive, $project])
            ->with('success', 'Project created successfully!');
    }

    /**
     * Display the specified project
     */
    public function show(Drive $drive, Project $project, Request $request)
    {
        $this->authorize('view', $drive);

        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        $view = $request->get('view', 'list'); // list, kanban, gantt, calendar, workload

        $project->load(['tasks.members', 'tasks.labels', 'tasks.owner', 'tasks.creator', 'tasks.attachments']);

        // Get tasks by status for kanban view
        $tasksByStatus = [
            'todo' => $project->tasks()->where('status', 'todo')->whereNull('deleted_at')->orderBy('sort_order')->get(),
            'in_progress' => $project->tasks()->where('status', 'in_progress')->whereNull('deleted_at')->orderBy('sort_order')->get(),
            'review' => $project->tasks()->where('status', 'review')->whereNull('deleted_at')->orderBy('sort_order')->get(),
            'done' => $project->tasks()->where('status', 'done')->whereNull('deleted_at')->orderBy('sort_order')->get(),
            'blocked' => $project->tasks()->where('status', 'blocked')->whereNull('deleted_at')->orderBy('sort_order')->get(),
        ];

        // Get drive members for task assignment
        $driveMembers = $drive->users()->get();
        
        // Get task labels
        $labels = $drive->taskLabels()->where('is_active', true)->get();

        // For workload view, prepare member statistics
        $memberStats = [];
        if ($view === 'workload') {
            foreach ($driveMembers as $member) {
                $memberTasks = $project->tasks()->whereNull('deleted_at')
                    ->where(function($query) use ($member) {
                        $query->where('owner_id', $member->id)
                            ->orWhereHas('taskMembers', function($q) use ($member) {
                                $q->where('user_id', $member->id);
                            });
                    })
                    ->get();
                
                $memberStats[$member->id] = [
                    'name' => $member->name,
                    'total' => $memberTasks->count(),
                    'in_progress' => $memberTasks->where('status', 'in_progress')->count(),
                    'done' => $memberTasks->where('status', 'done')->count(),
                    'overdue' => $memberTasks->filter(function($task) {
                        return $task->isOverdue();
                    })->count(),
                    'estimated_hours' => $memberTasks->sum('estimated_hours') ?? 0,
                ];
            }
        }

        return view('projects.show', compact('drive', 'project', 'view', 'tasksByStatus', 'driveMembers', 'labels', 'memberStats'));
    }

    /**
     * Show the form for editing the specified project
     */
    public function edit(Drive $drive, Project $project)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit projects.');
        }

        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        return view('projects.edit', compact('drive', 'project'));
    }

    /**
     * Update the specified project
     */
    public function update(Request $request, Drive $drive, Project $project)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit projects.');
        }

        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_public' => 'nullable|boolean',
            'status' => 'required|in:active,archived,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'header_image' => 'nullable|image|max:10240',
        ]);

        // Handle public key generation if making project public
        if ($request->has('is_public') && !$project->is_public) {
            $validated['public_key'] = Project::generatePublicKey();
        } elseif (!$request->has('is_public') && $project->is_public) {
            $validated['public_key'] = null;
        }

        $project->update($validated);

        // Handle header image upload
        if ($request->hasFile('header_image')) {
            // Delete old header image
            if ($project->header_image_path) {
                Storage::disk('public')->delete($project->header_image_path);
            }
            $this->storeHeaderImage($project, $request->file('header_image'));
        }

        return redirect()->route('drives.projects.projects.show', [$drive, $project])
            ->with('success', 'Project updated successfully!');
    }

    /**
     * Remove the specified project
     */
    public function destroy(Drive $drive, Project $project)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to delete
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot delete projects.');
        }

        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        // Delete header image if exists
        if ($project->header_image_path) {
            Storage::disk('public')->delete($project->header_image_path);
        }

        $project->delete();

        return redirect()->route('drives.projects.projects.index', $drive)
            ->with('success', 'Project deleted successfully!');
    }

    /**
     * Store header image for project
     */
    protected function storeHeaderImage(Project $project, $file)
    {
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = 'project-headers/' . $project->drive_id . '/' . $project->id;
        
        $filePath = $file->storeAs($path, $filename, 'public');

        $project->update([
            'header_image_path' => $filePath,
            'header_image_original_name' => $file->getClientOriginalName(),
        ]);
    }

    /**
     * Public view of project board
     */
    public function publicShow(string $publicKey)
    {
        $project = Project::where('public_key', $publicKey)
            ->where('is_public', true)
            ->where('status', 'active')
            ->firstOrFail();

        $project->load(['drive', 'tasks.members', 'tasks.labels', 'tasks.owner', 'tasks.attachments']);

        // Get tasks by status for kanban view
        $tasksByStatus = [
            'todo' => $project->tasks()->where('status', 'todo')->whereNull('deleted_at')->orderBy('sort_order')->get(),
            'in_progress' => $project->tasks()->where('status', 'in_progress')->whereNull('deleted_at')->orderBy('sort_order')->get(),
            'review' => $project->tasks()->where('status', 'review')->whereNull('deleted_at')->orderBy('sort_order')->get(),
            'done' => $project->tasks()->where('status', 'done')->whereNull('deleted_at')->orderBy('sort_order')->get(),
            'blocked' => $project->tasks()->where('status', 'blocked')->whereNull('deleted_at')->orderBy('sort_order')->get(),
        ];

        return view('projects.public', compact('project', 'tasksByStatus'));
    }
}
