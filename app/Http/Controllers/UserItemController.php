<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\UserItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserItemController extends Controller
{
    /**
     * Display a listing of user items for the drive
     */
    public function index(Drive $drive)
    {
        $this->authorize('view', $drive);

        $userItems = $drive->userItems()->orderBy('name')->get();

        return view('user-items.index', compact('drive', 'userItems'));
    }

    /**
     * Show the form for creating a new user item
     */
    public function create(Drive $drive)
    {
        $this->authorize('view', $drive);

        return view('user-items.create', compact('drive'));
    }

    /**
     * Store a newly created user item
     */
    public function store(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'default_price' => 'nullable|numeric|min:0',
        ]);

        $drive->userItems()->create(array_merge($validated, [
            'user_id' => Auth::id(),
        ]));

        return redirect()->route('drives.user-items.index', $drive)
            ->with('success', 'User item created successfully!');
    }

    /**
     * Display the specified user item
     */
    public function show(Drive $drive, UserItem $userItem)
    {
        $this->authorize('view', $drive);

        if ($userItem->drive_id !== $drive->id) {
            abort(404);
        }

        return view('user-items.show', compact('drive', 'userItem'));
    }

    /**
     * Show the form for editing the specified user item
     */
    public function edit(Drive $drive, UserItem $userItem)
    {
        $this->authorize('view', $drive);

        if ($userItem->drive_id !== $drive->id) {
            abort(404);
        }

        return view('user-items.edit', compact('drive', 'userItem'));
    }

    /**
     * Update the specified user item
     */
    public function update(Request $request, Drive $drive, UserItem $userItem)
    {
        $this->authorize('view', $drive);

        if ($userItem->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'default_price' => 'nullable|numeric|min:0',
        ]);

        $userItem->update($validated);

        return redirect()->route('drives.user-items.index', $drive)
            ->with('success', 'User item updated successfully!');
    }

    /**
     * Remove the specified user item
     */
    public function destroy(Drive $drive, UserItem $userItem)
    {
        $this->authorize('view', $drive);

        if ($userItem->drive_id !== $drive->id) {
            abort(404);
        }

        $userItem->delete();

        return redirect()->route('drives.user-items.index', $drive)
            ->with('success', 'User item deleted successfully!');
    }
}
