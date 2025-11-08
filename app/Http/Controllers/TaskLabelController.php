<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\TaskLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskLabelController extends Controller
{
    /**
     * Display a listing of task labels for the drive
     */
    public function index(Drive $drive)
    {
        $this->authorize('view', $drive);

        $labels = $drive->taskLabels()
            ->with('creator')
            ->orderBy('name')
            ->paginate(20);

        return view('task-labels.index', compact('drive', 'labels'));
    }

    /**
     * Show the form for creating a new task label
     */
    public function create(Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create task labels.');
        }
        
        return view('task-labels.create', compact('drive'));
    }

    /**
     * Store a newly created task label
     */
    public function store(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create task labels.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $label = $drive->taskLabels()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#6366F1',
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        // If AJAX request, return JSON response
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task label created successfully!',
                'label' => [
                    'id' => $label->id,
                    'name' => $label->name,
                    'color' => $label->color,
                    'description' => $label->description,
                ]
            ]);
        }

        return redirect()->route('drives.projects.task-labels.index', $drive)
            ->with('success', 'Task label created successfully!');
    }

    /**
     * Display the specified task label
     */
    public function show(Drive $drive, TaskLabel $taskLabel)
    {
        $this->authorize('view', $drive);

        if ($taskLabel->drive_id !== $drive->id) {
            abort(404);
        }

        $taskLabel->load(['creator', 'tasks' => function($query) {
            $query->whereNull('deleted_at')
                ->with('status')
                ->limit(50);
        }]);

        return view('task-labels.show', compact('drive', 'taskLabel'));
    }

    /**
     * Show the form for editing the specified task label
     */
    public function edit(Drive $drive, TaskLabel $taskLabel)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit task labels.');
        }

        if ($taskLabel->drive_id !== $drive->id) {
            abort(404);
        }

        return view('task-labels.edit', compact('drive', 'taskLabel'));
    }

    /**
     * Update the specified task label
     */
    public function update(Request $request, Drive $drive, TaskLabel $taskLabel)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit task labels.');
        }

        if ($taskLabel->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'nullable|boolean',
        ]);

        $taskLabel->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? $taskLabel->color,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('drives.projects.task-labels.show', [$drive, $taskLabel])
            ->with('success', 'Task label updated successfully!');
    }

    /**
     * Remove the specified task label
     */
    public function destroy(Drive $drive, TaskLabel $taskLabel)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to delete
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot delete task labels.');
        }

        if ($taskLabel->drive_id !== $drive->id) {
            abort(404);
        }

        $taskLabel->delete();

        return redirect()->route('drives.projects.task-labels.index', $drive)
            ->with('success', 'Task label deleted successfully!');
    }
}
