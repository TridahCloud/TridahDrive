<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\Project;
use App\Models\TaskCustomFieldDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaskCustomFieldController extends Controller
{
    /**
     * Store a newly created custom field definition
     */
    public function store(Request $request, Drive $drive, Project $project)
    {
        $this->authorize('view', $drive);
        
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create custom fields.');
        }

        if ($project->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,number,date,select,checkbox,textarea',
            'options' => 'nullable|string',
            'is_required' => 'boolean',
        ]);

        // Generate unique slug (unique per project)
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (TaskCustomFieldDefinition::where('project_id', $project->id)->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Parse options if provided
        $options = null;
        if ($validated['type'] === 'select' || $validated['type'] === 'checkbox') {
            if (!empty($validated['options'])) {
                $optionsList = array_filter(
                    array_map('trim', explode("\n", $validated['options']))
                );
                $options = array_values($optionsList);
            }
        }

        try {
            \Log::info('Creating custom field', [
                'project_id' => $project->id,
                'name' => $validated['name'],
                'type' => $validated['type'],
                'slug' => $slug,
                'options' => $options,
                'required' => $request->boolean('is_required', false),
            ]);
            
            $field = TaskCustomFieldDefinition::create([
                'project_id' => $project->id,
                'name' => $validated['name'],
                'slug' => $slug,
                'type' => $validated['type'],
                'options' => $options,
                'required' => $request->boolean('is_required', false),
                'is_active' => true,
            ]);
            
            \Log::info('Custom field created successfully', ['field_id' => $field->id]);
        } catch (\Exception $e) {
            \Log::error('Error creating custom field: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to create custom field: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to create custom field: ' . $e->getMessage());
        }

        // Return JSON response for AJAX/JSON requests, otherwise redirect
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'field' => [
                    'id' => $field->id,
                    'name' => $field->name,
                    'slug' => $field->slug,
                    'type' => $field->type,
                    'required' => (bool) $field->required,
                    'options' => $field->options,
                    'is_active' => (bool) $field->is_active,
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Custom field created successfully!');
    }

    /**
     * Remove a custom field definition
     */
    public function destroy(Drive $drive, Project $project, TaskCustomFieldDefinition $customField)
    {
        $this->authorize('view', $drive);
        
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot delete custom fields.');
        }

        if ($project->drive_id !== $drive->id || $customField->project_id !== $project->id) {
            abort(404);
        }

        $customField->delete();

        if (request()->expectsJson() || request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Custom field deleted successfully!');
    }
}
