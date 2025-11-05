<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\TimeLog;
use App\Models\Person;
use Illuminate\Http\Request;

class TimeLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Drive $drive, Request $request)
    {
        $this->authorize('view', $drive);

        $query = $drive->timeLogs()
            ->with(['person', 'schedule']);

        // Filter by person type
        if ($request->filled('person_type')) {
            $query->whereHas('person', function ($q) use ($request) {
                $q->where('type', $request->person_type);
            });
        }

        // Filter by person
        if ($request->filled('person_id')) {
            $query->where('person_id', $request->person_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
            $startDate = \Carbon\Carbon::parse($request->start_date, $userTimezone)
                ->setTimezone('UTC')
                ->startOfDay()
                ->format('Y-m-d');
            $query->where('work_date', '>=', $startDate);
        }

        if ($request->filled('end_date')) {
            $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
            $endDate = \Carbon\Carbon::parse($request->end_date, $userTimezone)
                ->setTimezone('UTC')
                ->endOfDay()
                ->format('Y-m-d');
            $query->where('work_date', '<=', $endDate);
        }

        $timeLogs = $query->orderBy('work_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get people for filter dropdown
        $people = $drive->people()
            ->where('status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('people-manager.time-logs.index', compact('drive', 'timeLogs', 'people'));
    }

    /**
     * Print report for a person's hours
     */
    public function printReport(Drive $drive, Person $person, Request $request)
    {
        $this->authorize('view', $drive);

        if ($person->drive_id !== $drive->id) {
            abort(404);
        }

        // Get user timezone for parsing dates
        $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
        $driveTimezone = $drive->getEffectiveTimezone();

        // Parse dates from user input (assumed in user's timezone) and convert to UTC for query
        if ($request->has('start_date') && $request->input('start_date')) {
            $startDate = \Carbon\Carbon::parse($request->input('start_date'), $userTimezone)
                ->setTimezone('UTC')
                ->startOfDay();
        } else {
            $startDate = \Carbon\Carbon::now()->startOfMonth()->setTimezone('UTC')->startOfDay();
        }

        if ($request->has('end_date') && $request->input('end_date')) {
            $endDate = \Carbon\Carbon::parse($request->input('end_date'), $userTimezone)
                ->setTimezone('UTC')
                ->endOfDay();
        } else {
            $endDate = \Carbon\Carbon::now()->endOfMonth()->setTimezone('UTC')->endOfDay();
        }

        $timeLogs = $drive->timeLogs()
            ->where('person_id', $person->id)
            ->whereBetween('work_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('work_date', 'asc')
            ->get();

        $totalHours = $timeLogs->sum('total_hours');
        $totalRegularHours = $timeLogs->sum('regular_hours');
        $totalOvertimeHours = $timeLogs->sum('overtime_hours');
        $totalPay = $timeLogs->sum('total_pay');

        // Convert dates back to user timezone for display
        $startDateForDisplay = $startDate->copy()->setTimezone($userTimezone);
        $endDateForDisplay = $endDate->copy()->setTimezone($userTimezone);

        return view('people-manager.time-logs.print-report', compact(
            'drive',
            'person',
            'timeLogs',
            'startDate',
            'endDate',
            'startDateForDisplay',
            'endDateForDisplay',
            'totalHours',
            'totalRegularHours',
            'totalOvertimeHours',
            'totalPay'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Drive $drive)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create time logs.');
        }

        $people = $drive->people()->where('status', 'active')->orderBy('last_name')->orderBy('first_name')->get();
        $schedules = $drive->schedules()->where('status', 'confirmed')->orderBy('start_date', 'desc')->get();

        return view('people-manager.time-logs.create', compact('drive', 'people', 'schedules'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create time logs.');
        }

        $validated = $request->validate([
            'person_id' => 'required|exists:people,id',
            'schedule_id' => 'nullable|exists:schedules,id',
            'work_date' => 'required|date',
            'clock_in' => 'nullable|date',
            'clock_out' => 'nullable|date|after:clock_in',
            'regular_hours' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'break_hours' => 'nullable|numeric|min:0',
            'total_hours' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:pending,approved,rejected',
            'notes' => 'nullable|string',
        ]);

        // Ensure person belongs to this drive
        $person = $drive->people()->find($validated['person_id']);
        if (!$person) {
            return back()->withErrors(['person_id' => 'Invalid person selected.'])->withInput();
        }

        // Ensure schedule belongs to this drive if provided
        if (isset($validated['schedule_id'])) {
            $schedule = $drive->schedules()->find($validated['schedule_id']);
            if (!$schedule) {
                return back()->withErrors(['schedule_id' => 'Invalid schedule selected.'])->withInput();
            }
        }

        // Convert dates from user timezone to drive timezone
        $driveTimezone = $drive->getEffectiveTimezone();
        $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
        
        // Parse work_date in user timezone, convert to drive timezone
        if (isset($validated['work_date'])) {
            $workDate = \Carbon\Carbon::parse($validated['work_date'], $userTimezone);
            $workDate->setTimezone($driveTimezone);
            $validated['work_date'] = $workDate->format('Y-m-d');
        }
        
        // Parse clock_in and clock_out in user timezone, convert to UTC for storage
        if (isset($validated['clock_in'])) {
            $clockIn = \Carbon\Carbon::parse($validated['clock_in'], $userTimezone);
            // Convert to UTC for storage (database stores UTC)
            $validated['clock_in'] = $clockIn->setTimezone('UTC')->format('Y-m-d H:i:s');
        }
        
        if (isset($validated['clock_out'])) {
            $clockOut = \Carbon\Carbon::parse($validated['clock_out'], $userTimezone);
            // Convert to UTC for storage (database stores UTC)
            $validated['clock_out'] = $clockOut->setTimezone('UTC')->format('Y-m-d H:i:s');
        }

        $timeLog = $drive->timeLogs()->create($validated);

        // Calculate pay if hours are provided (either from clock in/out or manual entry)
        if ($timeLog->total_hours > 0 || ($timeLog->clock_in && $timeLog->clock_out)) {
            // Reload person to ensure relationship is loaded
            $timeLog->load('person');
            $timeLog->calculateHoursAndPay();
            $timeLog->save();
        }

        return redirect()->route('drives.people-manager.time-logs.index', $drive)
            ->with('success', 'Time log created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Drive $drive, TimeLog $timeLog)
    {
        $this->authorize('view', $drive);

        if ($timeLog->drive_id !== $drive->id) {
            abort(404);
        }

        $timeLog->load(['person', 'schedule', 'approver']);

        return view('people-manager.time-logs.show', compact('drive', 'timeLog'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Drive $drive, TimeLog $timeLog)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit time logs.');
        }

        if ($timeLog->drive_id !== $drive->id) {
            abort(404);
        }

        $people = $drive->people()->where('status', 'active')->orderBy('last_name')->orderBy('first_name')->get();
        $schedules = $drive->schedules()->where('status', 'confirmed')->orderBy('start_date', 'desc')->get();

        return view('people-manager.time-logs.edit', compact('drive', 'timeLog', 'people', 'schedules'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Drive $drive, TimeLog $timeLog)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to update
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot update time logs.');
        }

        if ($timeLog->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'person_id' => 'required|exists:people,id',
            'schedule_id' => 'nullable|exists:schedules,id',
            'work_date' => 'required|date',
            'clock_in' => 'nullable|date',
            'clock_out' => 'nullable|date|after:clock_in',
            'regular_hours' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'break_hours' => 'nullable|numeric|min:0',
            'total_hours' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:pending,approved,rejected',
            'notes' => 'nullable|string',
        ]);

        // Ensure person belongs to this drive
        $person = $drive->people()->find($validated['person_id']);
        if (!$person) {
            return back()->withErrors(['person_id' => 'Invalid person selected.'])->withInput();
        }

        // Ensure schedule belongs to this drive if provided
        if (isset($validated['schedule_id'])) {
            $schedule = $drive->schedules()->find($validated['schedule_id']);
            if (!$schedule) {
                return back()->withErrors(['schedule_id' => 'Invalid schedule selected.'])->withInput();
            }
        }

        // Convert dates from user timezone to drive timezone
        $driveTimezone = $drive->getEffectiveTimezone();
        $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
        
        // Parse work_date in user timezone, convert to drive timezone
        if (isset($validated['work_date'])) {
            $workDate = \Carbon\Carbon::parse($validated['work_date'], $userTimezone);
            $workDate->setTimezone($driveTimezone);
            $validated['work_date'] = $workDate->format('Y-m-d');
        }
        
        // Parse clock_in and clock_out in user timezone, convert to UTC for storage
        if (isset($validated['clock_in'])) {
            $clockIn = \Carbon\Carbon::parse($validated['clock_in'], $userTimezone);
            // Convert to UTC for storage (database stores UTC)
            $validated['clock_in'] = $clockIn->setTimezone('UTC')->format('Y-m-d H:i:s');
        }
        
        if (isset($validated['clock_out'])) {
            $clockOut = \Carbon\Carbon::parse($validated['clock_out'], $userTimezone);
            // Convert to UTC for storage (database stores UTC)
            $validated['clock_out'] = $clockOut->setTimezone('UTC')->format('Y-m-d H:i:s');
        }

        $timeLog->update($validated);

        // Recalculate hours and pay if hours are provided (either from clock in/out or manual entry)
        if ($timeLog->total_hours > 0 || ($timeLog->clock_in && $timeLog->clock_out)) {
            // Reload person to ensure relationship is loaded
            $timeLog->load('person');
            $timeLog->calculateHoursAndPay();
            $timeLog->save();
        }

        return redirect()->route('drives.people-manager.time-logs.index', $drive)
            ->with('success', 'Time log updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Drive $drive, TimeLog $timeLog)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to delete
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot delete time logs.');
        }

        if ($timeLog->drive_id !== $drive->id) {
            abort(404);
        }

        $timeLog->delete();

        return redirect()->route('drives.people-manager.time-logs.index', $drive)
            ->with('success', 'Time log deleted successfully!');
    }

    /**
     * Approve a time log
     */
    public function approve(Request $request, Drive $drive, TimeLog $timeLog)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to approve
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot approve time logs.');
        }

        if ($timeLog->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'approval_notes' => 'nullable|string',
        ]);

        $timeLog->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $validated['approval_notes'] ?? null,
        ]);

        return redirect()->route('drives.people-manager.time-logs.index', $drive)
            ->with('success', 'Time log approved successfully!');
    }

    /**
     * Reject a time log
     */
    public function reject(Request $request, Drive $drive, TimeLog $timeLog)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to reject
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot reject time logs.');
        }

        if ($timeLog->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'approval_notes' => 'nullable|string',
        ]);

        $timeLog->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $validated['approval_notes'],
        ]);

        return redirect()->route('drives.people-manager.time-logs.index', $drive)
            ->with('success', 'Time log rejected.');
    }
}
