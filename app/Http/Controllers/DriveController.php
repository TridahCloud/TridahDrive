<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\Invoice;
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
        $drive->load(['items', 'users', 'toolProfiles']);

        // Group items by tool type
        $itemsByType = $drive->items()
            ->whereNull('deleted_at')
            ->latest()
            ->get()
            ->groupBy('tool_type');

        // Get invoice stats
        $invoiceStats = [
            'total' => $drive->invoices()->count(),
            'draft' => $drive->invoices()->where('status', 'draft')->count(),
            'paid' => $drive->invoices()->where('status', 'paid')->count(),
            'total_amount' => $drive->invoices()->where('status', 'paid')->sum('total'),
        ];

        return view('drives.show', compact('drive', 'itemsByType', 'invoiceStats'));
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
