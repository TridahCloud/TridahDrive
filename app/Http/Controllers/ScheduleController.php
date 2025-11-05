<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\Schedule;
use App\Models\Person;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Drive $drive)
    {
        $this->authorize('view', $drive);

        $schedules = $drive->schedules()
            ->with(['person', 'peopleManagerProfile'])
            ->orderBy('start_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->get();

        return view('people-manager.schedules.index', compact('drive', 'schedules'));
    }

    /**
     * Show the weekly schedule builder
     */
    public function builder(Drive $drive, Request $request)
    {
        $this->authorize('view', $drive);

        // Get week start date (default to this week's Monday)
        $weekStart = $request->input('week_start')
            ? \Carbon\Carbon::parse($request->input('week_start'))->startOfWeek()
            : \Carbon\Carbon::now()->startOfWeek();

        $weekEnd = $weekStart->copy()->endOfWeek();
        $daysOfWeek = [];
        $currentDay = $weekStart->copy();
        for ($i = 0; $i < 7; $i++) {
            $daysOfWeek[] = $currentDay->copy()->addDays($i);
        }

        // Get all active people
        $people = $drive->people()
            ->where('status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Get existing schedules for this week
        $existingSchedules = $drive->schedules()
            ->whereBetween('start_date', [$weekStart, $weekEnd])
            ->whereNotIn('status', ['cancelled'])
            ->with('person')
            ->get();

        // Group schedules by person and day
        $scheduleData = [];
        foreach ($existingSchedules as $schedule) {
            if ($schedule->person_id) {
                $dayKey = $schedule->start_date->format('Y-m-d');
                if (!isset($scheduleData[$schedule->person_id])) {
                    $scheduleData[$schedule->person_id] = [];
                }
                if (!isset($scheduleData[$schedule->person_id][$dayKey])) {
                    $scheduleData[$schedule->person_id][$dayKey] = [];
                }
                $scheduleData[$schedule->person_id][$dayKey][] = $schedule;
            }
        }

        return view('people-manager.schedules.builder', compact(
            'drive',
            'people',
            'daysOfWeek',
            'weekStart',
            'weekEnd',
            'scheduleData'
        ));
    }

    /**
     * Bulk create schedule assignments from builder
     */
    public function bulkCreate(Drive $drive, Request $request)
    {
        $this->authorize('view', $drive);

        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create schedules.');
        }

        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.person_id' => 'required|exists:people,id',
            'assignments.*.date' => 'required|date',
            'assignments.*.start_time' => 'required|string',
            'assignments.*.end_time' => 'required|string',
            'assignments.*.title' => 'nullable|string|max:255',
        ]);

        $created = 0;
        $errors = [];

        foreach ($validated['assignments'] as $assignment) {
            $person = $drive->people()->find($assignment['person_id']);
            if (!$person) {
                $errors[] = "Invalid person ID: {$assignment['person_id']}";
                continue;
            }

            try {
                // Get timezones
                $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
                
                // Parse date in user timezone at midnight, then convert to UTC for date-only storage
                $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', $assignment['date'], $userTimezone)->startOfDay();
                $startDate->setTimezone('UTC');
                
                // User enters times in their timezone (e.g., "09:00" for 9am EST)
                // Convert to UTC and store as UTC time
                // Example: User enters 9am EST → Convert to 2pm UTC → Store "14:00:00"
                
                // Parse user input: date + time in user's timezone
                $userStartDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $assignment['date'] . ' ' . $assignment['start_time'] . ':00', $userTimezone);
                $userEndDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $assignment['date'] . ' ' . $assignment['end_time'] . ':00', $userTimezone);
                
                // Convert to UTC for storage
                $utcStartDateTime = $userStartDateTime->copy()->setTimezone('UTC');
                $utcEndDateTime = $userEndDateTime->copy()->setTimezone('UTC');
                
                // Extract just the time portion (this is UTC time stored in database)
                $startTime = $utcStartDateTime->format('H:i:s');
                $endTime = $utcEndDateTime->format('H:i:s');
                
                // Calculate total hours based on actual datetime difference in user's timezone
                // (this ensures we get the correct duration regardless of timezone conversions)
                $totalMinutes = $userStartDateTime->diffInMinutes($userEndDateTime);
                $totalHours = round($totalMinutes / 60, 2);

                $schedule = $drive->schedules()->create([
                    'person_id' => $assignment['person_id'],
                    'title' => $assignment['title'] ?? 'Scheduled Shift',
                    'type' => 'one_time',
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $startDate->format('Y-m-d'),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => 'scheduled',
                    'total_hours' => $totalHours,
                    'break_minutes' => 0,
                ]);

                $created++;
            } catch (\Exception $e) {
                $errors[] = "Failed to create schedule for person {$assignment['person_id']}: " . $e->getMessage();
            }
        }

        $message = "Created {$created} schedule " . ($created === 1 ? 'entry' : 'entries') . ".";
        if (!empty($errors)) {
            $message .= " " . count($errors) . " " . (count($errors) === 1 ? 'error' : 'errors') . " occurred.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'created' => $created,
            'errors' => $errors,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Drive $drive)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create schedules.');
        }

        $people = $drive->people()->where('status', 'active')->orderBy('last_name')->orderBy('first_name')->get();
        $profiles = $drive->peopleManagerProfiles()->orderBy('name')->get();

        return view('people-manager.schedules.create', compact('drive', 'people', 'profiles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create schedules.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|max:255',
            'person_id' => 'nullable|exists:people,id',
            'people_manager_profile_id' => 'nullable|exists:people_manager_profiles,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'timezone' => 'nullable|string|max:255',
            'recurrence_pattern' => 'nullable|in:none,daily,weekly,monthly,yearly',
            'recurrence_days' => 'nullable|array',
            'recurrence_interval' => 'nullable|integer|min:1',
            'recurrence_end_date' => 'nullable|date',
            'recurrence_count' => 'nullable|integer|min:1',
            'status' => 'nullable|in:draft,confirmed,cancelled',
            'break_minutes' => 'nullable|integer|min:0',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Ensure person belongs to this drive
        if (isset($validated['person_id'])) {
            $person = $drive->people()->find($validated['person_id']);
            if (!$person) {
                return back()->withErrors(['person_id' => 'Invalid person selected.'])->withInput();
            }
        }

        // Ensure profile belongs to this drive
        if (isset($validated['people_manager_profile_id'])) {
            $profile = $drive->peopleManagerProfiles()->find($validated['people_manager_profile_id']);
            if (!$profile) {
                return back()->withErrors(['people_manager_profile_id' => 'Invalid profile selected.'])->withInput();
            }
        }

        // Convert dates from user timezone to UTC for date-only storage
        $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
        
        // Parse start_date in user timezone, convert to UTC for date-only storage
        if (isset($validated['start_date'])) {
            $startDate = \Carbon\Carbon::parse($validated['start_date'], $userTimezone);
            $startDate->setTimezone('UTC');
            $validated['start_date'] = $startDate->format('Y-m-d');
        }
        
        // Parse end_date in user timezone, convert to UTC for date-only storage
        if (isset($validated['end_date'])) {
            $endDate = \Carbon\Carbon::parse($validated['end_date'], $userTimezone);
            $endDate->setTimezone('UTC');
            $validated['end_date'] = $endDate->format('Y-m-d');
        }
        
        // Parse start_time and end_time - convert user input to UTC for storage
        if (isset($validated['start_date']) && isset($validated['start_time'])) {
            $userStartDateTime = \Carbon\Carbon::parse($validated['start_date'] . ' ' . $validated['start_time'], $userTimezone);
            $utcStartDateTime = $userStartDateTime->copy()->setTimezone('UTC');
            $validated['start_time'] = $utcStartDateTime->format('H:i:s');
        }
        
        if (isset($validated['end_time'])) {
            $endDate = $validated['end_date'] ?? $validated['start_date'];
            if ($endDate) {
                $userEndDateTime = \Carbon\Carbon::parse($endDate . ' ' . $validated['end_time'], $userTimezone);
                $utcEndDateTime = $userEndDateTime->copy()->setTimezone('UTC');
                $validated['end_time'] = $utcEndDateTime->format('H:i:s');
            }
        }

        $schedule = $drive->schedules()->create($validated);

        // Calculate total hours
        $schedule->total_hours = $schedule->calculateTotalHours();
        $schedule->save();

        return redirect()->route('drives.people-manager.schedules.index', $drive)
            ->with('success', 'Schedule created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Drive $drive, Schedule $schedule)
    {
        $this->authorize('view', $drive);

        if ($schedule->drive_id !== $drive->id) {
            abort(404);
        }

        $schedule->load(['person', 'peopleManagerProfile', 'timeLogs']);

        return view('people-manager.schedules.show', compact('drive', 'schedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Drive $drive, Schedule $schedule)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit schedules.');
        }

        if ($schedule->drive_id !== $drive->id) {
            abort(404);
        }

        $people = $drive->people()->where('status', 'active')->orderBy('last_name')->orderBy('first_name')->get();
        $profiles = $drive->peopleManagerProfiles()->orderBy('name')->get();

        return view('people-manager.schedules.edit', compact('drive', 'schedule', 'people', 'profiles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Drive $drive, Schedule $schedule)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to update
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot update schedules.');
        }

        if ($schedule->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|max:255',
            'person_id' => 'nullable|exists:people,id',
            'people_manager_profile_id' => 'nullable|exists:people_manager_profiles,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'timezone' => 'nullable|string|max:255',
            'recurrence_pattern' => 'nullable|in:none,daily,weekly,monthly,yearly',
            'recurrence_days' => 'nullable|array',
            'recurrence_interval' => 'nullable|integer|min:1',
            'recurrence_end_date' => 'nullable|date',
            'recurrence_count' => 'nullable|integer|min:1',
            'status' => 'nullable|in:draft,confirmed,cancelled',
            'break_minutes' => 'nullable|integer|min:0',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Ensure person belongs to this drive
        if (isset($validated['person_id'])) {
            $person = $drive->people()->find($validated['person_id']);
            if (!$person) {
                return back()->withErrors(['person_id' => 'Invalid person selected.'])->withInput();
            }
        }

        // Ensure profile belongs to this drive
        if (isset($validated['people_manager_profile_id'])) {
            $profile = $drive->peopleManagerProfiles()->find($validated['people_manager_profile_id']);
            if (!$profile) {
                return back()->withErrors(['people_manager_profile_id' => 'Invalid profile selected.'])->withInput();
            }
        }

        // Convert dates from user timezone to UTC for date-only storage
        $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
        
        // Parse start_date in user timezone, convert to UTC for date-only storage
        if (isset($validated['start_date'])) {
            $startDate = \Carbon\Carbon::parse($validated['start_date'], $userTimezone);
            $startDate->setTimezone('UTC');
            $validated['start_date'] = $startDate->format('Y-m-d');
        }
        
        // Parse end_date in user timezone, convert to UTC for date-only storage
        if (isset($validated['end_date'])) {
            $endDate = \Carbon\Carbon::parse($validated['end_date'], $userTimezone);
            $endDate->setTimezone('UTC');
            $validated['end_date'] = $endDate->format('Y-m-d');
        }
        
        // Parse start_time and end_time - convert user input to UTC for storage
        if (isset($validated['start_date']) && isset($validated['start_time'])) {
            $userStartDateTime = \Carbon\Carbon::parse($validated['start_date'] . ' ' . $validated['start_time'], $userTimezone);
            $utcStartDateTime = $userStartDateTime->copy()->setTimezone('UTC');
            $validated['start_time'] = $utcStartDateTime->format('H:i:s');
        }
        
        if (isset($validated['end_time'])) {
            $endDate = $validated['end_date'] ?? $validated['start_date'];
            if ($endDate) {
                $userEndDateTime = \Carbon\Carbon::parse($endDate . ' ' . $validated['end_time'], $userTimezone);
                $utcEndDateTime = $userEndDateTime->copy()->setTimezone('UTC');
                $validated['end_time'] = $utcEndDateTime->format('H:i:s');
            }
        }

        $schedule->update($validated);

        // Recalculate total hours
        $schedule->total_hours = $schedule->calculateTotalHours();
        $schedule->save();

        return redirect()->route('drives.people-manager.schedules.index', $drive)
            ->with('success', 'Schedule updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Drive $drive, Schedule $schedule)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to delete
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot delete schedules.');
        }

        if ($schedule->drive_id !== $drive->id) {
            abort(404);
        }

        $schedule->delete();

        // Return JSON response for AJAX requests
        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Schedule deleted successfully!']);
        }

        return redirect()->route('drives.people-manager.schedules.index', $drive)
            ->with('success', 'Schedule deleted successfully!');
    }
}
