<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\Person;
use App\Models\Schedule;
use App\Models\TimeLog;
use App\Models\PayrollEntry;
use Illuminate\Http\Request;

class PeopleManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Display the People Manager dashboard
     */
    public function dashboard(Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to view People Manager
        if (!$drive->userCanViewPeopleManager(auth()->user())) {
            abort(403, 'You do not have permission to access People Manager.');
        }

        // Get current month stats (check if tables exist)
        $dateFrom = now()->startOfMonth();
        $dateTo = now()->endOfMonth();

        try {
            $stats = [
                'total_people' => $drive->people()->count(),
                'active_people' => $drive->people()->where('status', 'active')->count(),
                'total_schedules' => $drive->schedules()
                    ->whereBetween('start_date', [$dateFrom, $dateTo])
                    ->count(),
                'pending_time_logs' => $drive->timeLogs()
                    ->where('status', 'pending')
                    ->count(),
                'total_hours_this_month' => $drive->timeLogs()
                    ->whereBetween('work_date', [$dateFrom, $dateTo])
                    ->where('status', 'approved')
                    ->sum('total_hours'),
                'total_payroll_this_month' => $drive->payrollEntries()
                    ->whereBetween('pay_date', [$dateFrom, $dateTo])
                    ->where('status', 'paid')
                    ->sum('net_pay'),
                'unsynced_payroll' => $drive->payrollEntries()
                    ->where('status', 'paid')
                    ->where('synced_to_bookkeeper', false)
                    ->count(),
            ];

            // Get recent activity
            $recentTimeLogs = $drive->timeLogs()
                ->with(['person'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $upcomingSchedules = $drive->schedules()
                ->with(['person'])
                ->where('start_date', '>=', now())
                ->orderBy('start_date', 'asc')
                ->limit(10)
                ->get();

            // Get people by type
            $peopleByType = [
                'employee' => $drive->people()->where('type', 'employee')->count(),
                'contractor' => $drive->people()->where('type', 'contractor')->count(),
                'volunteer' => $drive->people()->where('type', 'volunteer')->count(),
            ];

            // Check if profile exists
            $hasProfile = $drive->peopleManagerProfiles()->exists();
        } catch (\Exception $e) {
            // Tables don't exist yet - provide defaults
            $stats = [
                'total_people' => 0,
                'active_people' => 0,
                'total_schedules' => 0,
                'pending_time_logs' => 0,
                'total_hours_this_month' => 0,
                'total_payroll_this_month' => 0,
                'unsynced_payroll' => 0,
            ];
            $recentTimeLogs = collect([]);
            $upcomingSchedules = collect([]);
            $peopleByType = [
                'employee' => 0,
                'contractor' => 0,
                'volunteer' => 0,
            ];
            $hasProfile = false;
        }

        return view('people-manager.dashboard', compact(
            'drive',
            'stats',
            'recentTimeLogs',
            'upcomingSchedules',
            'peopleByType',
            'hasProfile'
        ));
    }
}
