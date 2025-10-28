<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\DriveItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DriveItemController extends Controller
{
    /**
     * Store a newly created item
     */
    public function store(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);
        $this->authorize('create', DriveItem::class);

        $validated = $request->validate([
            'tool_type' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'data' => 'nullable|array',
            'metadata' => 'nullable|array',
        ]);

        $item = DriveItem::create([
            'drive_id' => $drive->id,
            'tool_type' => $validated['tool_type'],
            'name' => $validated['name'],
            'data' => $validated['data'] ?? [],
            'metadata' => $validated['metadata'] ?? [],
            'created_by_id' => Auth::id(),
        ]);

        return redirect()->back()
            ->with('success', 'Item created successfully!');
    }

    /**
     * Update the specified item
     */
    public function update(Request $request, Drive $drive, DriveItem $item)
    {
        // Ensure item belongs to drive
        if ($item->drive_id !== $drive->id) {
            abort(404);
        }

        $this->authorize('update', $item);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'data' => 'nullable|array',
            'metadata' => 'nullable|array',
        ]);

        $item->update([
            'name' => $validated['name'],
            'data' => $validated['data'] ?? $item->data,
            'metadata' => $validated['metadata'] ?? $item->metadata,
            'updated_by_id' => Auth::id(),
        ]);

        return redirect()->back()
            ->with('success', 'Item updated successfully!');
    }

    /**
     * Remove the specified item
     */
    public function destroy(Drive $drive, DriveItem $item)
    {
        // Ensure item belongs to drive
        if ($item->drive_id !== $drive->id) {
            abort(404);
        }

        $this->authorize('delete', $item);

        $item->delete();

        return redirect()->back()
            ->with('success', 'Item deleted successfully!');
    }
}
