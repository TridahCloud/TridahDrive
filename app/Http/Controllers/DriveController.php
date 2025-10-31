<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\Invoice;
use App\Models\BookTransaction;
use App\Models\Task;
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
        $drive->load(['items', 'users', 'toolProfiles', 'invoices', 'projects', 'accounts']);

        // Group items by tool type (legacy support)
        $itemsByType = $drive->items()
            ->whereNull('deleted_at')
            ->latest()
            ->get()
            ->groupBy('tool_type');

        // Invoice stats
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

        return view('drives.show', compact(
            'drive', 
            'itemsByType', 
            'invoiceStats', 
            'recentInvoices',
            'bookkeeperStats',
            'recentTransactions',
            'projectStats',
            'recentProjects'
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

        return view('drives.edit', compact('drive'));
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
}
