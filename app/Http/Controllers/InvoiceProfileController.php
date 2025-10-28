<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\InvoiceProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceProfileController extends Controller
{
    /**
     * Display a listing of invoice profiles for the drive
     */
    public function index(Drive $drive)
    {
        $this->authorize('view', $drive);

        $profiles = $drive->invoiceProfiles()->orderBy('is_default', 'desc')->orderBy('name')->get();

        return view('invoice-profiles.index', compact('drive', 'profiles'));
    }

    /**
     * Show the form for creating a new invoice profile
     */
    public function create(Drive $drive)
    {
        $this->authorize('view', $drive);

        return view('invoice-profiles.create', compact('drive'));
    }

    /**
     * Store a newly created invoice profile
     */
    public function store(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_default' => 'sometimes|boolean',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_website' => 'nullable|url|max:255',
            'logo_url' => 'nullable|url|max:255',
            'invoice_prefix' => 'nullable|string|max:20',
            'next_invoice_number' => 'nullable|integer|min:1',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_routing_label' => 'nullable|string|max:50',
            'bank_routing_number' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
        ]);

        // If this is marked as default, unset other defaults
        if ($request->has('is_default') && $request->is_default) {
            $drive->invoiceProfiles()->update(['is_default' => false]);
        }

        $drive->invoiceProfiles()->create($validated);

        return redirect()->route('drives.invoice-profiles.index', $drive)
            ->with('success', 'Invoice profile created successfully!');
    }

    /**
     * Display the specified invoice profile
     */
    public function show(Drive $drive, InvoiceProfile $invoiceProfile)
    {
        $this->authorize('view', $drive);

        if ($invoiceProfile->drive_id !== $drive->id) {
            abort(404);
        }

        return view('invoice-profiles.show', compact('drive', 'invoiceProfile'));
    }

    /**
     * Show the form for editing the specified invoice profile
     */
    public function edit(Drive $drive, InvoiceProfile $invoiceProfile)
    {
        $this->authorize('view', $drive);

        if ($invoiceProfile->drive_id !== $drive->id) {
            abort(404);
        }

        return view('invoice-profiles.edit', compact('drive', 'invoiceProfile'));
    }

    /**
     * Update the specified invoice profile
     */
    public function update(Request $request, Drive $drive, InvoiceProfile $invoiceProfile)
    {
        $this->authorize('view', $drive);

        if ($invoiceProfile->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_default' => 'sometimes|boolean',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_website' => 'nullable|url|max:255',
            'logo_url' => 'nullable|url|max:255',
            'invoice_prefix' => 'nullable|string|max:20',
            'next_invoice_number' => 'nullable|integer|min:1',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_routing_label' => 'nullable|string|max:50',
            'bank_routing_number' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
            'accent_color' => 'nullable|string|max:7',
        ]);

        // If this is marked as default, unset other defaults
        if ($request->has('is_default') && $request->is_default) {
            $drive->invoiceProfiles()->where('id', '!=', $invoiceProfile->id)->update(['is_default' => false]);
        }

        $invoiceProfile->update($validated);

        return redirect()->route('drives.invoice-profiles.index', $drive)
            ->with('success', 'Invoice profile updated successfully!');
    }

    /**
     * Remove the specified invoice profile
     */
    public function destroy(Drive $drive, InvoiceProfile $invoiceProfile)
    {
        $this->authorize('view', $drive);

        if ($invoiceProfile->drive_id !== $drive->id) {
            abort(404);
        }

        $invoiceProfile->delete();

        return redirect()->route('drives.invoice-profiles.index', $drive)
            ->with('success', 'Invoice profile deleted successfully!');
    }
}
