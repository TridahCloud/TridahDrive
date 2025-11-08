<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\Project;
use App\Models\TaskStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskStatusController extends Controller
{
    public function store(Request $request, Drive $drive, Project $project): JsonResponse
    {
        $this->authorize('view', $drive);

        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot modify task statuses.');
        }

        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_completed' => 'nullable|boolean',
        ]);

        $sortOrder = ($project->taskStatuses()->max('sort_order') ?? 0) + 10;

        $status = $project->taskStatuses()->create([
            'name' => $data['name'],
            'slug' => TaskStatus::generateUniqueSlug($project->id, $data['name']),
            'color' => $data['color'] ?? '#6B7280',
            'is_completed' => $data['is_completed'] ?? false,
            'sort_order' => $sortOrder,
        ]);

        return response()->json([
            'success' => true,
            'status' => $status->fresh(),
        ], 201);
    }

    public function update(Request $request, Drive $drive, Project $project, TaskStatus $taskStatus): JsonResponse
    {
        $this->authorize('view', $drive);

        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot modify task statuses.');
        }

        if ($project->drive_id !== $drive->id || $taskStatus->project_id !== $project->id) {
            abort(404);
        }

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_completed' => 'nullable|boolean',
        ]);

        $taskStatus->update([
            'name' => $data['name'],
            'color' => $data['color'] ?? '#6B7280',
            'is_completed' => $data['is_completed'] ?? $taskStatus->is_completed,
        ]);

        return response()->json([
            'success' => true,
            'status' => $taskStatus->fresh(),
        ]);
    }

    public function destroy(Request $request, Drive $drive, Project $project, TaskStatus $taskStatus): JsonResponse
    {
        $this->authorize('view', $drive);

        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot modify task statuses.');
        }

        if ($project->drive_id !== $drive->id || $taskStatus->project_id !== $project->id) {
            abort(404);
        }

        if ($project->taskStatuses()->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'At least one status is required.',
            ], 422);
        }

        if ($taskStatus->tasks()->whereNull('deleted_at')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a status that still has tasks assigned.',
            ], 422);
        }

        $taskStatus->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function reorder(Request $request, Drive $drive, Project $project): JsonResponse
    {
        $this->authorize('view', $drive);

        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot modify task statuses.');
        }

        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => [
                'integer',
                Rule::exists('task_statuses', 'id')->where('project_id', $project->id),
            ],
        ]);

        $sortOrder = 0;
        foreach ($data['order'] as $statusId) {
            TaskStatus::where('id', $statusId)->update(['sort_order' => $sortOrder]);
            $sortOrder += 10;
        }

        return response()->json([
            'success' => true,
        ]);
    }
}


