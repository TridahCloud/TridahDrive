<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\Person;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PeopleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Drive $drive)
    {
        $this->authorize('view', $drive);

        $people = $drive->people()->with(['peopleManagerProfile', 'user'])->orderBy('last_name')->orderBy('first_name')->get();

        return view('people-manager.people.index', compact('drive', 'people'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Drive $drive)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create people.');
        }

        $profiles = $drive->peopleManagerProfiles()->orderBy('name')->get();
        
        // Check if user_id column exists (migration may not have run yet)
        $hasUserIdColumn = Schema::hasColumn('people', 'user_id');
        
        // Get all users who have access to this drive
        // Combine owner and members from pivot table
        $allDriveUserIds = [];
        
        // Add owner if exists
        if ($drive->owner_id) {
            $allDriveUserIds[] = $drive->owner_id;
        }
        
        // Add users from drive_users pivot table
        $pivotUserIds = $drive->users()->pluck('users.id')->toArray();
        $allDriveUserIds = array_unique(array_merge($allDriveUserIds, $pivotUserIds));
        
        $driveUsers = collect();
        
        if ($hasUserIdColumn) {
            // Filter out users who are already linked to people in this drive
            $linkedUserIds = $drive->people()
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->toArray();
            
            $availableUserIds = array_diff($allDriveUserIds, $linkedUserIds);
            
            // Get all available users
            if (!empty($availableUserIds)) {
                $driveUsers = \App\Models\User::whereIn('id', $availableUserIds)
                    ->orderBy('name')
                    ->get();
            }
        } else {
            // If column doesn't exist yet, show all Drive users (they just can't link yet)
            if (!empty($allDriveUserIds)) {
                $driveUsers = \App\Models\User::whereIn('id', $allDriveUserIds)
                    ->orderBy('name')
                    ->get();
            }
        }

        return view('people-manager.people.create', compact('drive', 'profiles', 'driveUsers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create people.');
        }

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'type' => 'required|in:employee,contractor,volunteer',
            'employee_id' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,terminated',
            'pay_type' => 'nullable|in:hourly,salary,contract,volunteer',
            'hourly_rate' => 'nullable|numeric|min:0',
            'salary_amount' => 'nullable|numeric|min:0',
            'salary_frequency' => 'nullable|in:weekly,biweekly,monthly,annually',
            'pay_frequency' => 'nullable|in:weekly,biweekly,monthly,custom',
            'people_manager_profile_id' => 'nullable|exists:people_manager_profiles,id',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Ensure user belongs to this drive
        if (isset($validated['user_id'])) {
            // Check if user_id column exists
            if (!Schema::hasColumn('people', 'user_id')) {
                return back()->withErrors(['user_id' => 'User linking feature is not available. Please run migrations.'])->withInput();
            }
            
            $selectedUser = User::find($validated['user_id']);
            if (!$selectedUser) {
                return back()->withErrors(['user_id' => 'Invalid user selected.'])->withInput();
            }
            
            // Check if user is owner or member of the drive
            $isOwner = $drive->owner_id === $selectedUser->id;
            $isMember = $drive->hasMember($selectedUser);
            
            if (!$isOwner && !$isMember) {
                return back()->withErrors(['user_id' => 'Selected user is not a member of this drive.'])->withInput();
            }
            
            // Check if user is already linked to another person in this drive
            $existingPerson = $drive->people()->where('user_id', $validated['user_id'])->first();
            if ($existingPerson) {
                return back()->withErrors(['user_id' => 'This user is already linked to another person in this drive.'])->withInput();
            }
            
            // Auto-populate name and email from user if not provided
            if (empty($validated['first_name'])) {
                $validated['first_name'] = $selectedUser->name;
            }
            if (empty($validated['email'])) {
                $validated['email'] = $selectedUser->email;
            }
        }

        $drive->people()->create($validated);

        return redirect()->route('drives.people-manager.people.index', $drive)
            ->with('success', 'Person created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Drive $drive, Person $person)
    {
        $this->authorize('view', $drive);

        if ($person->drive_id !== $drive->id) {
            abort(404);
        }

        $person->load(['peopleManagerProfile', 'schedules', 'timeLogs', 'payrollEntries']);

        return view('people-manager.people.show', compact('drive', 'person'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Drive $drive, Person $person)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit people.');
        }

        if ($person->drive_id !== $drive->id) {
            abort(404);
        }

        $profiles = $drive->peopleManagerProfiles()->orderBy('name')->get();
        
        // Check if user_id column exists (migration may not have run yet)
        $hasUserIdColumn = Schema::hasColumn('people', 'user_id');
        
        // Get all users who have access to this drive
        // Combine owner and members from pivot table
        $allDriveUserIds = [];
        
        // Add owner if exists
        if ($drive->owner_id) {
            $allDriveUserIds[] = $drive->owner_id;
        }
        
        // Add users from drive_users pivot table
        $pivotUserIds = $drive->users()->pluck('users.id')->toArray();
        $allDriveUserIds = array_unique(array_merge($allDriveUserIds, $pivotUserIds));
        
        $driveUsers = collect();
        
        if ($hasUserIdColumn) {
            // Filter out users who are already linked to other people in this drive
            $linkedUserIds = $drive->people()
                ->where('id', '!=', $person->id)
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->toArray();
            
            $availableUserIds = array_diff($allDriveUserIds, $linkedUserIds);
            
            // Always include the current person's user_id if they have one
            if ($person->user_id && !in_array($person->user_id, $availableUserIds)) {
                $availableUserIds[] = $person->user_id;
            }
            
            // Get all available users
            if (!empty($availableUserIds)) {
                $driveUsers = \App\Models\User::whereIn('id', $availableUserIds)
                    ->orderBy('name')
                    ->get();
            }
            
            // Load user relationship if exists
            $person->load('user');
        } else {
            // If column doesn't exist yet, show all Drive users (they just can't link yet)
            if (!empty($allDriveUserIds)) {
                $driveUsers = \App\Models\User::whereIn('id', $allDriveUserIds)
                    ->orderBy('name')
                    ->get();
            }
        }

        // Load user relationship if exists
        $person->load('user');

        return view('people-manager.people.edit', compact('drive', 'person', 'profiles', 'driveUsers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Drive $drive, Person $person)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to update
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot update people.');
        }

        if ($person->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'type' => 'required|in:employee,contractor,volunteer',
            'employee_id' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
            'termination_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,terminated',
            'pay_type' => 'nullable|in:hourly,salary,contract,volunteer',
            'hourly_rate' => 'nullable|numeric|min:0',
            'salary_amount' => 'nullable|numeric|min:0',
            'salary_frequency' => 'nullable|in:weekly,biweekly,monthly,annually',
            'pay_frequency' => 'nullable|in:weekly,biweekly,monthly,custom',
            'people_manager_profile_id' => 'nullable|exists:people_manager_profiles,id',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Ensure user belongs to this drive
        if (isset($validated['user_id'])) {
            // Check if user_id column exists
            if (!Schema::hasColumn('people', 'user_id')) {
                return back()->withErrors(['user_id' => 'User linking feature is not available. Please run migrations.'])->withInput();
            }
            
            $selectedUser = User::find($validated['user_id']);
            if (!$selectedUser) {
                return back()->withErrors(['user_id' => 'Invalid user selected.'])->withInput();
            }
            
            // Check if user is owner or member of the drive
            $isOwner = $drive->owner_id === $selectedUser->id;
            $isMember = $drive->hasMember($selectedUser);
            
            if (!$isOwner && !$isMember) {
                return back()->withErrors(['user_id' => 'Selected user is not a member of this drive.'])->withInput();
            }
            
            // Check if user is already linked to another person in this drive (excluding current person)
            $existingPerson = $drive->people()
                ->where('user_id', $validated['user_id'])
                ->where('id', '!=', $person->id)
                ->first();
            if ($existingPerson) {
                return back()->withErrors(['user_id' => 'This user is already linked to another person in this drive.'])->withInput();
            }
            
            // Auto-populate name and email from user if not provided
            if (empty($validated['first_name'])) {
                $validated['first_name'] = $selectedUser->name;
            }
            if (empty($validated['email'])) {
                $validated['email'] = $selectedUser->email;
            }
        }

        $person->update($validated);

        return redirect()->route('drives.people-manager.people.index', $drive)
            ->with('success', 'Person updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Drive $drive, Person $person)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to delete
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot delete people.');
        }

        if ($person->drive_id !== $drive->id) {
            abort(404);
        }

        $person->delete();

        return redirect()->route('drives.people-manager.people.index', $drive)
            ->with('success', 'Person deleted successfully!');
    }
}
