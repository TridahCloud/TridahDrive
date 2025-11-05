<?php

namespace App\Http\Controllers;

use App\Services\InvoiceBookKeeperSyncService;
use App\Models\BookTransaction;
use App\Models\Drive;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Client;
use App\Models\UserItem;
use App\Models\InvoiceProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceBookKeeperSyncService $syncService
    ) {}
    /**
     * Display a listing of invoices for the drive
     */
    public function index(Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to view Invoicer
        if (!$drive->userCanViewInvoicer(auth()->user())) {
            abort(403, 'You do not have permission to access Invoicer.');
        }

        $invoices = $drive->invoices()
            ->with(['client', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Load BookKeeper transactions for paid invoices to show sync status
        $paidInvoiceNumbers = $invoices->filter(fn($inv) => $inv->status === 'paid')
            ->pluck('invoice_number');
        
        $syncedTransactions = BookTransaction::where('drive_id', $drive->id)
            ->whereIn('reference', $paidInvoiceNumbers)
            ->pluck('reference')
            ->toArray();

        // Get stats for quick overview
        $stats = [
            'total' => $drive->invoices()->count(),
            'draft' => $drive->invoices()->where('status', 'draft')->count(),
            'sent' => $drive->invoices()->where('status', 'sent')->count(),
            'paid' => $drive->invoices()->where('status', 'paid')->count(),
            'total_revenue' => $drive->invoices()->where('status', 'paid')->sum('total'),
        ];

        // Check if invoice profile exists
        $hasProfile = $drive->invoiceProfiles()->exists();
        $hasClients = $drive->clients()->exists();
        $hasItems = $drive->userItems()->exists();

        return view('invoices.index', compact('drive', 'invoices', 'stats', 'hasProfile', 'hasClients', 'hasItems', 'syncedTransactions'));
    }

    /**
     * Show the form for creating a new invoice
     */
    public function create(Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create invoices.');
        }

        $invoiceProfile = $drive->default_invoice_profile;
        $clients = $drive->clients()->orderBy('name')->get();
        $userItems = $drive->userItems()->orderBy('name')->get();

        // Get the accent color from profile or use default
        $accentColor = $invoiceProfile?->accent_color ?? '#31d8b2';

        return view('invoices.create', compact('drive', 'invoiceProfile', 'clients', 'userItems', 'accentColor'));
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create invoices.');
        }

        $validated = $request->validate([
            'invoice_profile_id' => 'nullable|exists:invoice_profiles,id',
            'client_id' => 'nullable|exists:clients,id',
            'client_name' => 'required_without:client_id|max:255',
            'client_address' => 'nullable|string',
            'client_email' => 'nullable|email',
            'project' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'customizations' => 'nullable|string', // JSON string
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit' => 'nullable|string',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Get or use default profile
            $profile = $drive->invoiceProfiles()->find($request->invoice_profile_id)
                ?? $drive->default_invoice_profile;

            if (!$profile) {
                return redirect()->back()
                    ->withErrors(['error' => 'Please create an invoice profile first.']);
            }

            // Generate invoice number
            $invoiceNumber = $profile->getNextInvoiceNumber();

            // Parse customizations JSON if provided
            $customizations = null;
            if ($request->has('customizations') && !empty($request->customizations)) {
                $customizations = json_decode($request->customizations, true);
            }

            // Convert dates from user timezone to drive timezone
            $driveTimezone = $drive->getEffectiveTimezone();
            $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
            
            $issueDate = \Carbon\Carbon::parse($validated['issue_date'], $userTimezone);
            $issueDate->setTimezone($driveTimezone);
            $validated['issue_date'] = $issueDate->format('Y-m-d');
            
            $dueDate = $validated['due_date'] ?? $validated['issue_date'];
            $dueDate = \Carbon\Carbon::parse($dueDate, $userTimezone);
            $dueDate->setTimezone($driveTimezone);
            $validated['due_date'] = $dueDate->format('Y-m-d');

            // Create invoice
            $invoice = Invoice::create([
                'drive_id' => $drive->id,
                'user_id' => Auth::id(),
                'client_id' => $validated['client_id'] ?? null,
                'invoice_profile_id' => $profile->id,
                'invoice_number' => $invoiceNumber,
                'client_name' => $validated['client_name'] ?? '',
                'client_address' => $validated['client_address'] ?? '',
                'client_email' => $validated['client_email'] ?? '',
                'project' => $validated['project'] ?? null,
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'notes' => $validated['notes'] ?? '',
                'tax_rate' => $validated['tax_rate'] ?? 0,
                'customizations' => $customizations,
                'status' => 'draft',
            ]);

            // Create invoice items
            foreach ($validated['items'] as $index => $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? 'items',
                    'unit_price' => $item['unit_price'],
                    'item_order' => $index,
                ]);
            }

            // Calculate totals
            $invoice->calculateTotals();

            // Refresh invoice to get calculated totals
            $invoice->refresh();

            // Sync with BookKeeper if invoice is paid
            if ($invoice->status === 'paid') {
                $this->syncService->syncInvoice($invoice);
            }

            DB::commit();

            return redirect()->route('drives.invoices.show', [$drive, $invoice])
                ->with('success', 'Invoice created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create invoice: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified invoice
     */
    public function show(Drive $drive, Invoice $invoice)
    {
        $this->authorize('view', $drive);

        // Verify invoice belongs to drive
        if ($invoice->drive_id !== $drive->id) {
            abort(404);
        }

        $invoice->load(['client', 'items', 'invoiceProfile', 'user']);

        return view('invoices.show', compact('drive', 'invoice'));
    }

    /**
     * Show the form for editing the specified invoice
     */
    public function edit(Drive $drive, Invoice $invoice)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit invoices.');
        }

        if ($invoice->drive_id !== $drive->id) {
            abort(404);
        }

        $clients = $drive->clients()->orderBy('name')->get();
        $userItems = $drive->userItems()->orderBy('name')->get();
        $invoiceProfile = $invoice->invoiceProfile ?? $drive->default_invoice_profile;
        
        // Get the accent color from profile or use default
        $accentColor = $invoiceProfile?->accent_color ?? '#31d8b2';

        return view('invoices.edit', compact('drive', 'invoice', 'clients', 'userItems', 'invoiceProfile', 'accentColor'));
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, Drive $drive, Invoice $invoice)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit invoices.');
        }

        if ($invoice->drive_id !== $drive->id) {
            abort(404);
        }

        // If only status is being updated (from dropdown)
        // Check if this is a simple status update request
        if ($request->has('status') && !$request->has('items')) {
            $request->validate([
                'status' => 'required|in:draft,sent,paid,overdue,cancelled',
            ]);
            
            $oldStatus = $invoice->status;
            
            $invoice->update([
                'status' => $request->status,
            ]);
            
            // Sync with BookKeeper when status changes
            if ($oldStatus !== $invoice->status) {
                $this->syncService->syncInvoice($invoice);
            }
            
            return redirect()->route('drives.invoices.index', $drive)
                ->with('success', 'Invoice status updated successfully!');
        }

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'client_name' => 'required_without:client_id|max:255',
            'client_address' => 'nullable|string',
            'client_email' => 'nullable|email',
            'project' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'customizations' => 'nullable|string', // JSON string
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit' => 'nullable|string',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Get or use default profile
            $profile = $drive->invoiceProfiles()->find($request->invoice_profile_id)
                ?? $drive->default_invoice_profile;

            if (!$profile) {
                return redirect()->back()
                    ->withErrors(['error' => 'Please create an invoice profile first.']);
            }

            // Parse customizations JSON if provided
            $customizations = null;
            if ($request->has('customizations') && !empty($request->customizations)) {
                $customizations = json_decode($request->customizations, true);
            }

            // Convert dates from user timezone to drive timezone
            $driveTimezone = $drive->getEffectiveTimezone();
            $userTimezone = \App\Helpers\TimezoneHelper::getUserTimezone(auth()->user(), $drive);
            
            $issueDate = \Carbon\Carbon::parse($validated['issue_date'], $userTimezone);
            $issueDate->setTimezone($driveTimezone);
            $validated['issue_date'] = $issueDate->format('Y-m-d');
            
            $dueDate = \Carbon\Carbon::parse($validated['due_date'], $userTimezone);
            $dueDate->setTimezone($driveTimezone);
            $validated['due_date'] = $dueDate->format('Y-m-d');

            $invoice->update([
                'client_id' => $validated['client_id'] ?? null,
                'invoice_profile_id' => $profile->id,
                'client_name' => $validated['client_name'] ?? '',
                'client_address' => $validated['client_address'] ?? '',
                'client_email' => $validated['client_email'] ?? '',
                'project' => $validated['project'] ?? null,
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'notes' => $validated['notes'] ?? '',
                'tax_rate' => $validated['tax_rate'] ?? 0,
                'customizations' => $customizations,
                'status' => $validated['status'],
            ]);

            // Delete existing items
            $invoice->items()->delete();

            // Create new items
            foreach ($validated['items'] as $index => $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? 'items',
                    'unit_price' => $item['unit_price'],
                    'item_order' => $index,
                ]);
            }

            // Calculate totals
            $invoice->calculateTotals();

            // Refresh invoice to get calculated totals
            $invoice->refresh();

            // Sync with BookKeeper when invoice is updated
            $this->syncService->syncInvoice($invoice);

            DB::commit();

            return redirect()->route('drives.invoices.show', [$drive, $invoice])
                ->with('success', 'Invoice updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update invoice: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified invoice
     */
    public function destroy(Drive $drive, Invoice $invoice)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to delete
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot delete invoices.');
        }

        if ($invoice->drive_id !== $drive->id) {
            abort(404);
        }

        // Remove associated BookKeeper transaction if exists
        $this->syncService->syncInvoice($invoice);

        $invoice->delete();

        return redirect()->route('drives.invoices.index', $drive)
            ->with('success', 'Invoice deleted successfully!');
    }
}
