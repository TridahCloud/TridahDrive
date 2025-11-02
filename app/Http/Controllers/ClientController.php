<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Display a listing of clients for the drive
     */
    public function index(Drive $drive)
    {
        $this->authorize('view', $drive);

        $clients = $drive->clients()->orderBy('name')->get();

        return view('clients.index', compact('drive', 'clients'));
    }

    /**
     * Show the form for creating a new client
     */
    public function create(Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create clients.');
        }

        return view('clients.create', compact('drive'));
    }

    /**
     * Store a newly created client
     */
    public function store(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create clients.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $drive->clients()->create(array_merge($validated, [
            'user_id' => Auth::id(),
        ]));

        return redirect()->route('drives.clients.index', $drive)
            ->with('success', 'Client created successfully!');
    }

    /**
     * Display the specified client
     */
    public function show(Drive $drive, Client $client)
    {
        $this->authorize('view', $drive);

        if ($client->drive_id !== $drive->id) {
            abort(404);
        }

        return view('clients.show', compact('drive', 'client'));
    }

    /**
     * Show the form for editing the specified client
     */
    public function edit(Drive $drive, Client $client)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit clients.');
        }

        if ($client->drive_id !== $drive->id) {
            abort(404);
        }

        return view('clients.edit', compact('drive', 'client'));
    }

    /**
     * Update the specified client
     */
    public function update(Request $request, Drive $drive, Client $client)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit clients.');
        }

        if ($client->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $client->update($validated);

        return redirect()->route('drives.clients.index', $drive)
            ->with('success', 'Client updated successfully!');
    }

    /**
     * Remove the specified client
     */
    public function destroy(Drive $drive, Client $client)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to delete
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot delete clients.');
        }

        if ($client->drive_id !== $drive->id) {
            abort(404);
        }

        $client->delete();

        return redirect()->route('drives.clients.index', $drive)
            ->with('success', 'Client deleted successfully!');
    }
}
