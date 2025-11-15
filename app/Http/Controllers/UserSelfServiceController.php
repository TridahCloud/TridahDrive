<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\Schedule;
use App\Models\TimeLog;
use App\Models\Person;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserSelfServiceController extends Controller
{
    /**
     * Show user's schedules for a drive
     */
    public function schedules(Drive $drive)
    {
        // Find the person record linked to the current user
        $person = $drive->people()->where('user_id', auth()->id())->first();
        
        if (!$person) {
            abort(403, 'You are not linked to a person in this Drive. Please contact an administrator.');
        }

        // Check permission to view schedules
        if (!$drive->userHasPermission(auth()->user(), 'mytime.view_own_schedules')) {
            abort(403, 'You do not have permission to view schedules.');
        }

        // Get schedules - filter based on permissions
        $query = $drive->schedules()->where('person_id', $person->id);
        
        // If user can only view their own schedules, they already see only theirs (filtered above)
        // If they can view all schedules, we could expand this later
        
        // Get upcoming schedules (from today onwards)
        $upcomingSchedules = (clone $query)
            ->where('start_date', '>=', Carbon::today())
            ->orderBy('start_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        // Get past schedules (last 30 days)
        $pastSchedules = (clone $query)
            ->where('start_date', '<', Carbon::today())
            ->where('start_date', '>=', Carbon::today()->subDays(30))
            ->orderBy('start_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        return view('user-self-service.schedules', compact('drive', 'person', 'upcomingSchedules', 'pastSchedules'));
    }

    /**
     * Show user's time logs for a drive
     */
    public function timeLogs(Drive $drive, Request $request)
    {
        // Find the person record linked to the current user
        $person = $drive->people()->where('user_id', auth()->id())->first();
        
        if (!$person) {
            abort(403, 'You are not linked to a person in this Drive. Please contact an administrator.');
        }

        // Check permission to view time logs
        if (!$drive->userHasPermission(auth()->user(), 'mytime.view_own_time_logs')) {
            abort(403, 'You do not have permission to view time logs.');
        }

        $query = $drive->timeLogs()
            ->where('person_id', $person->id)
            ->with(['schedule']);

        // Filter by date range if provided
        if ($request->filled('start_date')) {
            $query->where('work_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('work_date', '<=', $request->end_date);
        }

        $timeLogs = $query->orderBy('work_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user-self-service.time-logs', compact('drive', 'person', 'timeLogs'));
    }

    /**
     * Clock in for a schedule
     */
    public function clockIn(Request $request, Drive $drive, Schedule $schedule)
    {
        // Find the person record linked to the current user
        $person = $drive->people()->where('user_id', auth()->id())->first();
        
        if (!$person) {
            abort(403, 'You are not linked to a person in this Drive.');
        }

        // Verify schedule belongs to this person and drive
        if ($schedule->person_id !== $person->id || $schedule->drive_id !== $drive->id) {
            abort(403, 'You do not have permission to clock in for this schedule.');
        }

        // Only allow clocking in for scheduled or confirmed schedules
        if (!in_array($schedule->status, ['scheduled', 'confirmed'])) {
            return back()->with('error', 'You can only clock in for scheduled or confirmed shifts.');
        }

        // Use the schedule's date, not today
        $scheduleDate = Carbon::parse($schedule->start_date);
        
        // Check if there's already an active time log for this schedule on this date
        $existingTimeLog = $drive->timeLogs()
            ->where('person_id', $person->id)
            ->where('schedule_id', $schedule->id)
            ->where('work_date', $scheduleDate->format('Y-m-d'))
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->first();

        if ($existingTimeLog) {
            return back()->with('error', 'You have already clocked in for this shift. Please clock out first.');
        }

        // Create or update time log
        $timeLog = $drive->timeLogs()->firstOrCreate(
            [
                'person_id' => $person->id,
                'schedule_id' => $schedule->id,
                'work_date' => $scheduleDate->format('Y-m-d'),
            ],
            [
                'status' => 'pending',
            ]
        );

        // Clock in - use current time in UTC (database stores UTC)
        // Times will be displayed in the schedule's timezone
        $timeLog->clock_in = Carbon::now('UTC');
        $timeLog->save();

        return back()->with('success', 'You have successfully clocked in!');
    }

    /**
     * Clock out for a schedule
     */
    public function clockOut(Request $request, Drive $drive, Schedule $schedule)
    {
        // Find the person record linked to the current user
        $person = $drive->people()->where('user_id', auth()->id())->first();
        
        if (!$person) {
            abort(403, 'You are not linked to a person in this Drive.');
        }

        // Verify schedule belongs to this person and drive
        if ($schedule->person_id !== $person->id || $schedule->drive_id !== $drive->id) {
            abort(403, 'You do not have permission to clock out for this schedule.');
        }

        // Use the schedule's date, not today
        $scheduleDate = Carbon::parse($schedule->start_date);
        
        // Find active time log for this schedule on this date
        $timeLog = $drive->timeLogs()
            ->where('person_id', $person->id)
            ->where('schedule_id', $schedule->id)
            ->where('work_date', $scheduleDate->format('Y-m-d'))
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->first();

        if (!$timeLog) {
            return back()->with('error', 'You must clock in before clocking out.');
        }

        // Clock out - use current time in UTC (database stores UTC)
        // Times will be displayed in the schedule's timezone
        $timeLog->clock_out = Carbon::now('UTC');
        
        // Calculate hours and pay
        $timeLog->load('person');
        $timeLog->calculateHoursAndPay();
        $timeLog->save();

        return back()->with('success', 'You have successfully clocked out! Total hours: ' . number_format($timeLog->total_hours, 2));
    }

    /**
     * Show form to create/edit time log for a schedule
     */
    public function createTimeLogForSchedule(Drive $drive, Schedule $schedule)
    {
        // Find the person record linked to the current user
        $person = $drive->people()->where('user_id', auth()->id())->first();
        
        if (!$person) {
            abort(403, 'You are not linked to a person in this Drive.');
        }

        // Verify schedule belongs to this person and drive
        if ($schedule->person_id !== $person->id || $schedule->drive_id !== $drive->id) {
            abort(403, 'You do not have permission to create a time log for this schedule.');
        }

        // Get or create time log for this schedule
        $scheduleDate = Carbon::parse($schedule->start_date);
        $timeLog = $drive->timeLogs()
            ->where('person_id', $person->id)
            ->where('schedule_id', $schedule->id)
            ->where('work_date', $scheduleDate->format('Y-m-d'))
            ->first();

        if (!$timeLog) {
            // Create a new time log
            $timeLog = $drive->timeLogs()->create([
                'person_id' => $person->id,
                'schedule_id' => $schedule->id,
                'work_date' => $scheduleDate->format('Y-m-d'),
                'status' => 'pending',
            ]);
        }

        // Redirect to edit
        return redirect()->route('user-self-service.edit-time-log', [$drive, $timeLog]);
    }

    /**
     * Show form to edit time log
     */
    public function editTimeLog(Drive $drive, TimeLog $timeLog)
    {
        // Find the person record linked to the current user
        $person = $drive->people()->where('user_id', auth()->id())->first();
        
        if (!$person) {
            abort(403, 'You are not linked to a person in this Drive.');
        }

        // Verify time log belongs to this person and drive
        if ($timeLog->person_id !== $person->id || $timeLog->drive_id !== $drive->id) {
            abort(403, 'You do not have permission to edit this time log.');
        }

        // Only allow editing if it's for a scheduled shift and not approved
        if ($timeLog->status === 'approved') {
            return back()->with('error', 'You cannot edit an approved time log. Please contact an administrator.');
        }

        $timeLog->load(['schedule']);

        return view('user-self-service.edit-time-log', compact('drive', 'person', 'timeLog'));
    }

    /**
     * Update time log
     */
    public function updateTimeLog(Request $request, Drive $drive, TimeLog $timeLog)
    {
        // Find the person record linked to the current user
        $person = $drive->people()->where('user_id', auth()->id())->first();
        
        if (!$person) {
            abort(403, 'You are not linked to a person in this Drive.');
        }

        // Verify time log belongs to this person and drive
        if ($timeLog->person_id !== $person->id || $timeLog->drive_id !== $drive->id) {
            abort(403, 'You do not have permission to edit this time log.');
        }

        // Only allow editing if not approved
        if ($timeLog->status === 'approved') {
            return back()->with('error', 'You cannot edit an approved time log.');
        }

        $validated = $request->validate([
            'clock_in' => 'nullable|date',
            'clock_out' => 'nullable|date|after:clock_in',
            'break_hours' => 'nullable|numeric|min:0',
            'total_hours' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'work_description' => 'nullable|string',
        ]);

        // Load schedule to get timezone
        $timeLog->load('schedule');
        $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);

        // Convert clock in/out times from user timezone to UTC for storage
        // User enters times in their timezone, we convert to UTC for database storage
        if (isset($validated['clock_in']) && $validated['clock_in']) {
            $validated['clock_in'] = Carbon::parse($validated['clock_in'], $userTimezone)
                ->setTimezone('UTC')
                ->toDateTimeString();
        }

        if (isset($validated['clock_out']) && $validated['clock_out']) {
            $validated['clock_out'] = Carbon::parse($validated['clock_out'], $userTimezone)
                ->setTimezone('UTC')
                ->toDateTimeString();
        }

        // Update time log
        $timeLog->update($validated);

        // Recalculate hours and pay if clock in/out or hours are provided
        if (($timeLog->clock_in && $timeLog->clock_out) || $timeLog->total_hours > 0) {
            $timeLog->load('person');
            $timeLog->calculateHoursAndPay();
            $timeLog->save();
        }

        return redirect()->route('user-self-service.time-logs', $drive)
            ->with('success', 'Time log updated successfully!');
    }
}

