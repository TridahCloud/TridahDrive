<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\Project;
use App\Models\TaskTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskTemplateController extends Controller
{
    /**
     * Display a listing of task templates
     */
    public function index(Drive $drive, Project $project)
    {
        $this->authorize('view', $drive);
        
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot access templates.');
        }

        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        $templates = TaskTemplate::where('drive_id', $drive->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('task-templates.index', compact('drive', 'project', 'templates'));
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request, Drive $drive, Project $project)
    {
        $this->authorize('view', $drive);
        
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create templates.');
        }

        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'default_labels' => 'nullable|array',
            'default_labels.*' => 'exists:task_labels,id',
            'default_members' => 'nullable|array',
            'default_members.*' => 'exists:users,id',
            'template_description' => 'nullable|string',
            'estimated_hours' => 'nullable|integer|min:0',
        ]);

        $template = TaskTemplate::create([
            'drive_id' => $drive->id,
            'created_by' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'default_labels' => $validated['default_labels'] ?? [],
            'default_members' => $validated['default_members'] ?? [],
            'template_description' => $validated['template_description'] ?? null,
            'estimated_hours' => $validated['estimated_hours'] ?? null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'template' => $template]);
        }

        return redirect()->back()->with('success', 'Template created successfully!');
    }

    /**
     * Create a task from a template
     */
    public function createTaskFromTemplate(Request $request, Drive $drive, Project $project, TaskTemplate $taskTemplate)
    {
        $this->authorize('view', $drive);
        
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create tasks.');
        }

        if ($project->drive_id !== $drive->id || $taskTemplate->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'status_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('task_statuses', 'id')->where('project_id', $project->id),
            ],
        ]);

        // Get default status if not provided
        $statusId = $validated['status_id'] ?? $project->taskStatuses()->orderBy('sort_order')->first()?->id;

        $maxSortOrder = $project->tasks()
            ->where('task_status_id', $statusId)
            ->whereNull('deleted_at')
            ->max('sort_order') ?? -1;

        $task = $project->tasks()->create([
            'title' => $validated['title'],
            'description' => $taskTemplate->template_description,
            'task_status_id' => $statusId,
            'priority' => $taskTemplate->priority,
            'estimated_hours' => $taskTemplate->estimated_hours,
            'sort_order' => $maxSortOrder + 1,
            'created_by' => Auth::id(),
        ]);

        // Attach default labels
        if (!empty($taskTemplate->default_labels)) {
            $task->labels()->attach($taskTemplate->default_labels);
        }

        // Attach default members
        if (!empty($taskTemplate->default_members)) {
            foreach ($taskTemplate->default_members as $userId) {
                $task->taskMembers()->create([
                    'user_id' => $userId,
                    'assigned_by' => Auth::id(),
                ]);
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'task_id' => $task->id]);
        }

        return redirect()->route('drives.projects.projects.show', [$drive, $project])
            ->with('success', 'Task created from template!');
    }
}
