<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\Invoice;
use App\Models\BookTransaction;
use App\Models\Task;
use App\Models\Person;
use App\Models\Schedule;
use App\Models\TimeLog;
use App\Models\PayrollEntry;
use App\Services\DriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DriveController extends Controller
{
    public function __construct(
        protected DriveService $driveService
    ) {}

    /**
     * Display a listing of drives
     */
    public function index()
    {
        $user = Auth::user();
        
        // Ensure personal drive exists
        $user->getPersonalDrive();

        $personalDrives = $user->personalDrives()->get();
        $sharedDrives = $user->sharedDrives()->get();
        
        return view('drives.index', compact('personalDrives', 'sharedDrives'));
    }

    /**
     * Show the form for creating a new drive
     */
    public function create()
    {
        return view('drives.create');
    }

    /**
     * Store a newly created drive
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:50',
        ]);

        $drive = $this->driveService->createSharedDrive(Auth::user(), $validated);

        return redirect()->route('drives.show', $drive)
            ->with('success', 'Shared drive created successfully!');
    }

    /**
     * Display the specified drive
     */
    public function show(Drive $drive)
    {
        $this->authorize('view', $drive);

        // Load relationships
        $drive->load(['items', 'users', 'toolProfiles', 'invoices', 'projects', 'accounts', 'subDrives', 'parentDrive']);

        // Group items by tool type (legacy support)
        $itemsByType = $drive->items()
            ->whereNull('deleted_at')
            ->latest()
            ->get()
            ->groupBy('tool_type');

        // Invoice stats (include sub-drives if this is a parent drive)
        if (!$drive->isSubDrive()) {
            $invoiceStats = [
                'total' => $drive->getInvoicesIncludingSubDrives()->count(),
                'draft' => $drive->getInvoicesIncludingSubDrives()->where('status', 'draft')->count(),
                'paid' => $drive->getInvoicesIncludingSubDrives()->where('status', 'paid')->count(),
                'total_amount' => $drive->getInvoicesIncludingSubDrives()->where('status', 'paid')->sum('total'),
            ];
            
            // Recent invoices (last 5) including sub-drives
            $recentInvoices = $drive->getInvoicesIncludingSubDrives()
                ->with(['client', 'user'])
                ->latest()
                ->take(5)
                ->get();
            
            // BookKeeper stats including sub-drives
            $bookkeeperStats = [
                'total_transactions' => $drive->getTransactionsIncludingSubDrives()->count(),
                'total_accounts' => $drive->accounts()->where('is_active', true)->count(),
                'total_categories' => $drive->categories()->where('is_active', true)->count(),
            ];
            
            // Recent transactions (last 5) including sub-drives
            $recentTransactions = $drive->getTransactionsIncludingSubDrives()
                ->with(['account', 'category', 'creator'])
                ->latest()
                ->take(5)
                ->get();
            
            // Project Board stats including sub-drives
            $projectStats = [
                'total_projects' => $drive->getProjectsIncludingSubDrives()->whereNull('deleted_at')->count(),
                'active_projects' => $drive->getProjectsIncludingSubDrives()->whereNull('deleted_at')->where('status', 'active')->count(),
                'total_tasks' => Task::whereHas('project', function($query) use ($drive) {
                    $driveIds = $drive->getDriveIdsIncludingSubDrives();
                    $query->whereIn('drive_id', $driveIds)->whereNull('deleted_at');
                })->whereNull('deleted_at')->count(),
            ];
            
            // Recent projects (last 5) including sub-drives
            $recentProjects = $drive->getProjectsIncludingSubDrives()
                ->whereNull('deleted_at')
                ->with(['creator', 'tasks' => function($query) {
                    $query->whereNull('deleted_at');
                }])
                ->latest()
                ->take(5)
                ->get();
            
            // People Manager stats including sub-drives (check if tables exist)
            try {
                $peopleManagerStats = [
                    'total_people' => $drive->getPeopleIncludingSubDrives()->count(),
                    'active_people' => $drive->getPeopleIncludingSubDrives()->where('status', 'active')->count(),
                    'total_schedules' => $drive->getSchedulesIncludingSubDrives()
                        ->whereBetween('start_date', [now()->startOfMonth(), now()->endOfMonth()])
                        ->count(),
                    'pending_time_logs' => $drive->getTimeLogsIncludingSubDrives()->where('status', 'pending')->count(),
                ];
                
                // Recent people (last 5) including sub-drives
                $recentPeople = $drive->getPeopleIncludingSubDrives()
                    ->with(['peopleManagerProfile'])
                    ->latest()
                    ->take(5)
                    ->get();
            } catch (\Exception $e) {
                // Tables don't exist yet - provide defaults
                $peopleManagerStats = [
                    'total_people' => 0,
                    'active_people' => 0,
                    'total_schedules' => 0,
                    'pending_time_logs' => 0,
                ];
                $recentPeople = collect([]);
            }
        } else {
            // For sub-drives, don't include parent or siblings
            $invoiceStats = [
                'total' => $drive->invoices()->count(),
                'draft' => $drive->invoices()->where('status', 'draft')->count(),
                'paid' => $drive->invoices()->where('status', 'paid')->count(),
                'total_amount' => $drive->invoices()->where('status', 'paid')->sum('total'),
            ];
            
            // Recent invoices (last 5)
            $recentInvoices = $drive->invoices()
                ->with(['client', 'user'])
                ->latest()
                ->take(5)
                ->get();
            
            // BookKeeper stats
            $bookkeeperStats = [
                'total_transactions' => $drive->bookTransactions()->count(),
                'total_accounts' => $drive->accounts()->where('is_active', true)->count(),
                'total_categories' => $drive->categories()->where('is_active', true)->count(),
            ];
            
            // Recent transactions (last 5)
            $recentTransactions = $drive->bookTransactions()
                ->with(['account', 'category', 'creator'])
                ->latest()
                ->take(5)
                ->get();
            
            // Project Board stats
            $projectStats = [
                'total_projects' => $drive->projects()->whereNull('deleted_at')->count(),
                'active_projects' => $drive->projects()->whereNull('deleted_at')->where('status', 'active')->count(),
                'total_tasks' => Task::whereHas('project', function($query) use ($drive) {
                    $query->where('drive_id', $drive->id)->whereNull('deleted_at');
                })->whereNull('deleted_at')->count(),
            ];
            
            // Recent projects (last 5)
            $recentProjects = $drive->projects()
                ->whereNull('deleted_at')
                ->with(['creator', 'tasks' => function($query) {
                    $query->whereNull('deleted_at');
                }])
                ->latest()
                ->take(5)
                ->get();
            
            // People Manager stats (sub-drive only) - check if tables exist
            try {
                $peopleManagerStats = [
                    'total_people' => $drive->people()->count(),
                    'active_people' => $drive->people()->where('status', 'active')->count(),
                    'total_schedules' => $drive->schedules()
                        ->whereBetween('start_date', [now()->startOfMonth(), now()->endOfMonth()])
                        ->count(),
                    'pending_time_logs' => $drive->timeLogs()->where('status', 'pending')->count(),
                ];
                
                // Recent people (last 5)
                $recentPeople = $drive->people()
                    ->with(['peopleManagerProfile'])
                    ->latest()
                    ->take(5)
                    ->get();
            } catch (\Exception $e) {
                // Tables don't exist yet - provide defaults
                $peopleManagerStats = [
                    'total_people' => 0,
                    'active_people' => 0,
                    'total_schedules' => 0,
                    'pending_time_logs' => 0,
                ];
                $recentPeople = collect([]);
            }
        }

        return view('drives.show', compact(
            'drive', 
            'itemsByType', 
            'invoiceStats', 
            'recentInvoices',
            'bookkeeperStats',
            'recentTransactions',
            'projectStats',
            'recentProjects',
            'peopleManagerStats',
            'recentPeople'
        ));
    }

    /**
     * Show the form for editing the specified drive
     */
    public function edit(Drive $drive)
    {
        $this->authorize('update', $drive);

        // Load users for member management
        $drive->load('users');
        
        // Load roles and people for role assignment section
        $roles = $drive->roles()->orderBy('name')->get();
        $people = $drive->people()->with('user')->orderBy('first_name')->orderBy('last_name')->get();
        $driveUsers = $drive->users()->where('users.id', '!=', $drive->owner_id)->orderBy('name')->get();

        return view('drives.edit', compact('drive', 'roles', 'people', 'driveUsers'));
    }

    /**
     * Update the specified drive
     */
    public function update(Request $request, Drive $drive)
    {
        $this->authorize('update', $drive);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:50',
            'currency' => 'nullable|string|size:3',
            'timezone' => 'nullable|string|max:50',
        ]);

        $this->driveService->updateDrive($drive, Auth::user(), $validated);

        return redirect()->route('drives.show', $drive)
            ->with('success', 'Drive updated successfully!');
    }

    /**
     * Remove the specified drive
     */
    public function destroy(Drive $drive)
    {
        $this->authorize('delete', $drive);

        $this->driveService->deleteDrive($drive, Auth::user());

        return redirect()->route('drives.index')
            ->with('success', 'Drive deleted successfully!');
    }

    /**
     * Store a new sub-drive
     */
    public function storeSubDrive(Request $request, Drive $drive)
    {
        $this->authorize('update', $drive);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:50',
        ]);

        try {
            $subDrive = $this->driveService->createSubDrive($drive, Auth::user(), $validated);
            return redirect()->route('drives.show', $subDrive)
                ->with('success', 'Sub-drive created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Update drive settings including hidden sub-drives
     */
    public function updateSettings(Request $request, Drive $drive)
    {
        $this->authorize('update', $drive);

        $validated = $request->validate([
            'hidden_sub_drives' => 'nullable|array',
            'hidden_sub_drives.*' => 'exists:drives,id',
        ]);

        $settings = $drive->settings ?? [];
        
        // Always set hidden_sub_drives - empty array if not present (all checkboxes unchecked)
        $settings['hidden_sub_drives'] = $validated['hidden_sub_drives'] ?? [];

        $drive->update(['settings' => $settings]);

        return redirect()->back()
            ->with('success', 'Settings updated successfully!');
    }
}
