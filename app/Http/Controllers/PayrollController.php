<?php

namespace App\Http\Controllers;

use App\Models\Drive;
use App\Models\PayrollEntry;
use App\Models\Person;
use App\Services\PayrollBookKeeperSyncService;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Drive $drive)
    {
        $this->authorize('view', $drive);

        $payrollEntries = $drive->payrollEntries()
            ->with(['person', 'peopleManagerProfile'])
            ->orderBy('pay_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('people-manager.payroll.index', compact('drive', 'payrollEntries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Drive $drive)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create payroll entries.');
        }

        $people = $drive->people()->where('status', 'active')->orderBy('last_name')->orderBy('first_name')->get();
        $profiles = $drive->peopleManagerProfiles()->orderBy('name')->get();

        return view('people-manager.payroll.create', compact('drive', 'people', 'profiles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create payroll entries.');
        }

        $validated = $request->validate([
            'person_id' => 'required|exists:people,id',
            'people_manager_profile_id' => 'nullable|exists:people_manager_profiles,id',
            'payroll_period' => 'nullable|string|max:255',
            'period_start_date' => 'required|date',
            'period_end_date' => 'required|date|after_or_equal:period_start_date',
            'pay_date' => 'required|date',
            'regular_hours' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'total_hours' => 'nullable|numeric|min:0',
            'regular_pay' => 'nullable|numeric|min:0',
            'overtime_pay' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'gross_pay' => 'nullable|numeric|min:0',
            'federal_tax' => 'nullable|numeric|min:0',
            'state_tax' => 'nullable|numeric|min:0',
            'local_tax' => 'nullable|numeric|min:0',
            'social_security' => 'nullable|numeric|min:0',
            'medicare' => 'nullable|numeric|min:0',
            'retirement_contribution' => 'nullable|numeric|min:0',
            'health_insurance' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'total_deductions' => 'nullable|numeric|min:0',
            'net_pay' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:direct_deposit,check,cash,other',
            'payment_reference' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,pending,processed,paid,cancelled',
            'notes' => 'nullable|string',
        ]);

        // Ensure person belongs to this drive
        $person = $drive->people()->find($validated['person_id']);
        if (!$person) {
            return back()->withErrors(['person_id' => 'Invalid person selected.'])->withInput();
        }

        // Ensure profile belongs to this drive if provided
        if (isset($validated['people_manager_profile_id'])) {
            $profile = $drive->peopleManagerProfiles()->find($validated['people_manager_profile_id']);
            if (!$profile) {
                return back()->withErrors(['people_manager_profile_id' => 'Invalid profile selected.'])->withInput();
            }
        }

        // Generate payroll_period if not provided
        if (empty($validated['payroll_period'])) {
            $startDate = \Carbon\Carbon::parse($validated['period_start_date']);
            $endDate = \Carbon\Carbon::parse($validated['period_end_date']);
            
            // Format: "Jan 1, 2025 to Jan 15, 2025" or "Jan 2025" if same month
            if ($startDate->format('Y-m') === $endDate->format('Y-m')) {
                $validated['payroll_period'] = $startDate->format('M Y');
            } else {
                $validated['payroll_period'] = $startDate->format('M j, Y') . ' to ' . $endDate->format('M j, Y');
            }
        }

        $payrollEntry = $drive->payrollEntries()->create($validated);

        // Only recalculate net_pay if it wasn't manually provided
        // This allows users to manually enter net_pay if needed
        // Check if net_pay was explicitly provided in the request
        if (!$request->has('net_pay') || $request->input('net_pay') === null || $request->input('net_pay') === '') {
            $payrollEntry->calculateNetPay();
            $payrollEntry->save();
        }

        return redirect()->route('drives.people-manager.payroll.index', $drive)
            ->with('success', 'Payroll entry created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Drive $drive, PayrollEntry $payroll)
    {
        $this->authorize('view', $drive);

        if ($payroll->drive_id !== $drive->id) {
            abort(404);
        }

        $payroll->load(['person', 'peopleManagerProfile', 'bookTransaction']);

        $payrollEntry = $payroll; // Alias for view compatibility
        return view('people-manager.payroll.show', compact('drive', 'payrollEntry'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Drive $drive, PayrollEntry $payroll)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit payroll entries.');
        }

        if ($payroll->drive_id !== $drive->id) {
            abort(404);
        }

        $people = $drive->people()->where('status', 'active')->orderBy('last_name')->orderBy('first_name')->get();
        $profiles = $drive->peopleManagerProfiles()->orderBy('name')->get();

        $payrollEntry = $payroll; // Alias for view compatibility
        return view('people-manager.payroll.edit', compact('drive', 'payrollEntry', 'people', 'profiles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Drive $drive, PayrollEntry $payroll)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to update
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot update payroll entries.');
        }

        if ($payroll->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validate([
            'person_id' => 'required|exists:people,id',
            'people_manager_profile_id' => 'nullable|exists:people_manager_profiles,id',
            'payroll_period' => 'nullable|string|max:255',
            'period_start_date' => 'required|date',
            'period_end_date' => 'required|date|after_or_equal:period_start_date',
            'pay_date' => 'required|date',
            'regular_hours' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'total_hours' => 'nullable|numeric|min:0',
            'regular_pay' => 'nullable|numeric|min:0',
            'overtime_pay' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'gross_pay' => 'nullable|numeric|min:0',
            'federal_tax' => 'nullable|numeric|min:0',
            'state_tax' => 'nullable|numeric|min:0',
            'local_tax' => 'nullable|numeric|min:0',
            'social_security' => 'nullable|numeric|min:0',
            'medicare' => 'nullable|numeric|min:0',
            'retirement_contribution' => 'nullable|numeric|min:0',
            'health_insurance' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'total_deductions' => 'nullable|numeric|min:0',
            'net_pay' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:direct_deposit,check,cash,other',
            'payment_reference' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,pending,processed,paid,cancelled',
            'notes' => 'nullable|string',
        ]);

        // Ensure person belongs to this drive
        $person = $drive->people()->find($validated['person_id']);
        if (!$person) {
            return back()->withErrors(['person_id' => 'Invalid person selected.'])->withInput();
        }

        // Ensure profile belongs to this drive if provided
        if (isset($validated['people_manager_profile_id'])) {
            $profile = $drive->peopleManagerProfiles()->find($validated['people_manager_profile_id']);
            if (!$profile) {
                return back()->withErrors(['people_manager_profile_id' => 'Invalid profile selected.'])->withInput();
            }
        }

        // Generate payroll_period if not provided
        if (empty($validated['payroll_period'])) {
            $startDate = \Carbon\Carbon::parse($validated['period_start_date']);
            $endDate = \Carbon\Carbon::parse($validated['period_end_date']);
            
            // Format: "Jan 1, 2025 to Jan 15, 2025" or "Jan 2025" if same month
            if ($startDate->format('Y-m') === $endDate->format('Y-m')) {
                $validated['payroll_period'] = $startDate->format('M Y');
            } else {
                $validated['payroll_period'] = $startDate->format('M j, Y') . ' to ' . $endDate->format('M j, Y');
            }
        }

        $payroll->update($validated);

        // Only recalculate net_pay if it wasn't manually provided
        // This allows users to manually enter net_pay if needed
        // Check if net_pay was explicitly provided in the request (even if 0, that's still a valid manual entry)
        if (!$request->has('net_pay') || $request->input('net_pay') === null || $request->input('net_pay') === '') {
            $payroll->calculateNetPay();
            $payroll->save();
        }

        return redirect()->route('drives.people-manager.payroll.index', $drive)
            ->with('success', 'Payroll entry updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Drive $drive, PayrollEntry $payroll)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to delete
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot delete payroll entries.');
        }

        if ($payroll->drive_id !== $drive->id) {
            abort(404);
        }

        // If synced to BookKeeper, warn but still allow deletion
        if ($payroll->synced_to_bookkeeper) {
            // Optionally remove the transaction from BookKeeper
            // For now, we'll just delete the payroll entry
            // The transaction will remain in BookKeeper (orphaned)
        }

        $payroll->delete();

        return redirect()->route('drives.people-manager.payroll.index', $drive)
            ->with('success', 'Payroll entry deleted successfully!');
    }

    /**
     * Sync payroll entry to BookKeeper
     */
    public function syncToBookKeeper(Drive $drive, PayrollEntry $payrollEntry)
    {
        $this->authorize('view', $drive);

        // Check if user has permission to sync
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot sync payroll entries.');
        }

        if ($payrollEntry->drive_id !== $drive->id) {
            abort(404);
        }

        // Check if payroll entry has required data
        if (!$payrollEntry->person) {
            return redirect()->route('drives.people-manager.payroll.index', $drive)
                ->with('error', 'Cannot sync: Payroll entry must have a person assigned.');
        }

        if (!$payrollEntry->net_pay || $payrollEntry->net_pay <= 0) {
            return redirect()->route('drives.people-manager.payroll.index', $drive)
                ->with('error', 'Cannot sync: Payroll entry must have a net pay amount greater than 0.');
        }

        $isReSync = $payrollEntry->synced_to_bookkeeper;

        try {
            $syncService = new PayrollBookKeeperSyncService();
            $transaction = $syncService->syncPayrollEntry($payrollEntry);

            if ($transaction) {
                $message = $isReSync 
                    ? 'Payroll entry re-synced to BookKeeper successfully! Transaction updated: ' . $transaction->transaction_number
                    : 'Payroll entry synced to BookKeeper successfully! Transaction: ' . $transaction->transaction_number;
                
                return redirect()->route('drives.people-manager.payroll.index', $drive)
                    ->with('success', $message);
            } else {
                return redirect()->route('drives.people-manager.payroll.index', $drive)
                    ->with('warning', 'Payroll entry is not marked as paid. Only paid entries can be synced to BookKeeper. Please mark the entry as "paid" first.');
            }
        } catch (\Exception $e) {
            \Log::error('Payroll sync error: ' . $e->getMessage(), [
                'payroll_entry_id' => $payrollEntry->id,
                'drive_id' => $drive->id,
                'exception' => $e
            ]);

            return redirect()->route('drives.people-manager.payroll.index', $drive)
                ->with('error', 'Failed to sync payroll entry: ' . $e->getMessage());
        }
    }

    /**
     * Mark payroll entry as paid
     */
    public function markAsPaid(Drive $drive, PayrollEntry $payroll)
    {
        $this->authorize('view', $drive);

        // Check if user has permission
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot update payroll entries.');
        }

        if ($payroll->drive_id !== $drive->id) {
            abort(404);
        }

        $payroll->update([
            'status' => 'paid',
        ]);

        return redirect()->route('drives.people-manager.payroll.index', $drive)
            ->with('success', 'Payroll entry marked as paid successfully!');
    }

    /**
     * Mark payroll entry as unpaid (draft)
     */
    public function markAsUnpaid(Drive $drive, PayrollEntry $payroll)
    {
        $this->authorize('view', $drive);

        // Check if user has permission
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot update payroll entries.');
        }

        if ($payroll->drive_id !== $drive->id) {
            abort(404);
        }

        $payroll->update([
            'status' => 'draft',
        ]);

        return redirect()->route('drives.people-manager.payroll.index', $drive)
            ->with('success', 'Payroll entry marked as unpaid.');
    }

    /**
     * Mark payroll entry as paid and sync to BookKeeper in one action
     */
    public function markAsPaidAndSync(Drive $drive, PayrollEntry $payrollEntry)
    {
        $this->authorize('view', $drive);

        // Check if user has permission
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot update payroll entries.');
        }

        if ($payrollEntry->drive_id !== $drive->id) {
            abort(404);
        }

        // Check if payroll entry has required data
        if (!$payrollEntry->person) {
            return redirect()->route('drives.people-manager.payroll.index', $drive)
                ->with('error', 'Cannot sync: Payroll entry must have a person assigned.');
        }

        if (!$payrollEntry->net_pay || $payrollEntry->net_pay <= 0) {
            return redirect()->route('drives.people-manager.payroll.index', $drive)
                ->with('error', 'Cannot sync: Payroll entry must have a net pay amount greater than 0.');
        }

        // Mark as paid
        $payrollEntry->update([
            'status' => 'paid',
        ]);

        // Sync to BookKeeper
        try {
            $syncService = new PayrollBookKeeperSyncService();
            $transaction = $syncService->syncPayrollEntry($payrollEntry);

            if ($transaction) {
                return redirect()->route('drives.people-manager.payroll.index', $drive)
                    ->with('success', 'Payroll entry marked as paid and synced to BookKeeper! Transaction: ' . $transaction->transaction_number);
            } else {
                return redirect()->route('drives.people-manager.payroll.index', $drive)
                    ->with('warning', 'Payroll entry marked as paid, but sync failed. Please try syncing manually.');
            }
        } catch (\Exception $e) {
            \Log::error('Payroll sync error: ' . $e->getMessage(), [
                'payroll_entry_id' => $payrollEntry->id,
                'drive_id' => $drive->id,
                'exception' => $e
            ]);

            return redirect()->route('drives.people-manager.payroll.index', $drive)
                ->with('warning', 'Payroll entry marked as paid, but sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate payroll entries from approved time logs
     */
    public function generateFromTimeLogs(Request $request, Drive $drive)
    {
        $this->authorize('view', $drive);

        // Check if user has permission
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot generate payroll entries.');
        }

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'person_id' => 'nullable|exists:people,id',
        ]);

        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);

        // Get approved time logs in the date range
        $query = $drive->timeLogs()
            ->where('status', 'approved')
            ->whereBetween('work_date', [$startDate, $endDate]);

        if ($validated['person_id']) {
            $query->where('person_id', $validated['person_id']);
        }

        $timeLogs = $query->with('person')->get();

        if ($timeLogs->isEmpty()) {
            return redirect()->route('drives.people-manager.payroll.index', $drive)
                ->with('warning', 'No approved time logs found for the selected period.');
        }

        // Group time logs by person and create payroll entries
        $grouped = $timeLogs->groupBy('person_id');
        $created = 0;
        $errors = [];

        foreach ($grouped as $personId => $logs) {
            $person = $logs->first()->person;
            
            if (!$person) {
                continue;
            }

            // Calculate totals for this person
            $totalRegularHours = $logs->sum('regular_hours');
            $totalOvertimeHours = $logs->sum('overtime_hours');
            $totalHours = $logs->sum('total_hours');
            
            // Generate payroll period
            $firstDate = $logs->min('work_date');
            $lastDate = $logs->max('work_date');
            
            // Calculate pay based on person's pay type
            if ($person->pay_type === 'salary' && $person->salary_amount && $person->salary_frequency) {
                // For salary employees, use the SELECTED period dates (not time log dates)
                // Salary is based on pay periods, not when they worked
                $salaryStartDate = $startDate;
                $salaryEndDate = $endDate;
                
                // Use prorated salary for the selected period
                $salaryAmount = $person->getProratedSalaryForPeriod($salaryStartDate, $salaryEndDate);
                $totalRegularPay = $salaryAmount ?? 0;
                $totalOvertimePay = 0; // Salary employees typically don't get overtime
                $totalPay = $totalRegularPay;
                
                // Update period dates to match selected period for salary employees
                $firstDate = $salaryStartDate;
                $lastDate = $salaryEndDate;
            } else {
                // For hourly/contract employees, sum up time log pay amounts
                // Use actual time log dates for period
                $totalRegularPay = $logs->sum('regular_pay') ?? 0;
                $totalOvertimePay = $logs->sum('overtime_pay') ?? 0;
                $totalPay = $logs->sum('total_pay') ?? 0;
            }
            
            if ($firstDate->format('Y-m') === $lastDate->format('Y-m')) {
                $payrollPeriod = $firstDate->format('M Y');
            } else {
                $payrollPeriod = $firstDate->format('M j, Y') . ' to ' . $lastDate->format('M j, Y');
            }

            // Check if payroll entry already exists for this period
            $existing = $drive->payrollEntries()
                ->where('person_id', $personId)
                ->where('period_start_date', $firstDate->format('Y-m-d'))
                ->where('period_end_date', $lastDate->format('Y-m-d'))
                ->first();

            if ($existing) {
                $errors[] = "Payroll entry already exists for {$person->full_name} ({$payrollPeriod})";
                continue;
            }

            try {
                $payrollEntry = $drive->payrollEntries()->create([
                    'person_id' => $personId,
                    'people_manager_profile_id' => $person->people_manager_profile_id,
                    'payroll_period' => $payrollPeriod,
                    'period_start_date' => $firstDate,
                    'period_end_date' => $lastDate,
                    'pay_date' => $endDate->copy()->addDays(7)->startOfWeek()->addDays(4), // Next Friday after period end
                    'regular_hours' => $totalRegularHours,
                    'overtime_hours' => $totalOvertimeHours,
                    'total_hours' => $totalHours,
                    'regular_pay' => $totalRegularPay,
                    'overtime_pay' => $totalOvertimePay,
                    'gross_pay' => $totalRegularPay + $totalOvertimePay,
                    'net_pay' => $totalPay, // Will be recalculated after deductions
                    'status' => 'draft',
                    'payment_method' => $person->pay_type === 'volunteer' ? 'other' : 'direct_deposit',
                ]);

                // Recalculate net pay (will subtract deductions if any)
                $payrollEntry->calculateNetPay();
                $payrollEntry->save();

                $created++;
            } catch (\Exception $e) {
                $errors[] = "Failed to create payroll for {$person->full_name}: " . $e->getMessage();
            }
        }

        $message = "Generated {$created} payroll " . ($created === 1 ? 'entry' : 'entries') . " from time logs.";
        if (!empty($errors)) {
            $message .= " " . count($errors) . " " . (count($errors) === 1 ? 'error' : 'errors') . " occurred.";
        }

        return redirect()->route('drives.people-manager.payroll.index', $drive)
            ->with('success', $message)
            ->with('errors', $errors);
    }
}
