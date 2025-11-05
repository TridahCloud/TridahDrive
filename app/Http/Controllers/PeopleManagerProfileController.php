<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\PeopleManagerProfile;
use Illuminate\Http\Request;

class PeopleManagerProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Drive $drive)
    {
        $this->authorize('view', $drive);

        $profiles = $drive->peopleManagerProfiles()->orderBy('is_default', 'desc')->orderBy('name')->get();

        return view('people-manager.profiles.index', compact('drive', 'profiles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Drive $drive)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create people manager profiles.');
        }

        return view('people-manager.profiles.create', compact('drive'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create people manager profiles.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_default' => 'sometimes|boolean',
            'organization_name' => 'nullable|string|max:255',
            'organization_address' => 'nullable|string',
            'organization_phone' => 'nullable|string|max:255',
            'organization_email' => 'nullable|email|max:255',
            'default_pay_frequency' => 'nullable|in:weekly,biweekly,monthly,custom',
            'default_overtime_threshold' => 'nullable|numeric|min:0|max:168',
            'default_overtime_multiplier' => 'nullable|numeric|min:1|max:3',
            'accent_color' => 'nullable|string|max:7',
        ]);

        // If this is marked as default, unset other defaults
        if ($request->has('is_default') && $request->is_default) {
            $drive->peopleManagerProfiles()->update(['is_default' => false]);
        }

        $drive->peopleManagerProfiles()->create($validated);

        return redirect()->route('drives.people-manager.profiles.index', $drive)
            ->with('success', 'People Manager profile created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Drive $drive, PeopleManagerProfile $profile)
    {
        $this->authorize('view', $drive);

        if ($profile->drive_id !== $drive->id) {
            abort(404);
        }

        return view('people-manager.profiles.show', compact('drive', 'profile'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Drive $drive, PeopleManagerProfile $profile)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit people manager profiles.');
        }

        if ($profile->drive_id !== $drive->id) {
            abort(404);
        }

        return view('people-manager.profiles.edit', compact('drive', 'profile'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Drive $drive, PeopleManagerProfile $profile)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to update
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot update people manager profiles.');
        }

        if ($profile->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_default' => 'sometimes|boolean',
            'organization_name' => 'nullable|string|max:255',
            'organization_address' => 'nullable|string',
            'organization_phone' => 'nullable|string|max:255',
            'organization_email' => 'nullable|email|max:255',
            'default_pay_frequency' => 'nullable|in:weekly,biweekly,monthly,custom',
            'default_overtime_threshold' => 'nullable|numeric|min:0|max:168',
            'default_overtime_multiplier' => 'nullable|numeric|min:1|max:3',
            'accent_color' => 'nullable|string|max:7',
        ]);

        // If this is marked as default, unset other defaults
        if ($request->has('is_default') && $request->is_default) {
            $drive->peopleManagerProfiles()->where('id', '!=', $profile->id)->update(['is_default' => false]);
        }

        $profile->update($validated);

        return redirect()->route('drives.people-manager.profiles.index', $drive)
            ->with('success', 'People Manager profile updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Drive $drive, PeopleManagerProfile $profile)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to delete
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot delete people manager profiles.');
        }

        if ($profile->drive_id !== $drive->id) {
            abort(404);
        }

        $profile->delete();

        return redirect()->route('drives.people-manager.profiles.index', $drive)
            ->with('success', 'People Manager profile deleted successfully!');
    }
}
