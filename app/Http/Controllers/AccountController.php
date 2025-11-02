<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Drive;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts for the drive
     */
    public function index(Drive $drive)
    {
        $this->authorize('view', $drive);

        $accounts = $drive->accounts()
            ->with(['parent', 'children'])
            ->whereNull('parent_id')
            ->orderBy('type')
            ->orderBy('account_code')
            ->get();

        return view('bookkeeper.accounts.index', compact('drive', 'accounts'));
    }

    /**
     * Show the form for creating a new account
     */
    public function create(Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create accounts.');
        }

        $parentAccounts = $drive->accounts()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('bookkeeper.accounts.create', compact('drive', 'parentAccounts'));
    }

    /**
     * Store a newly created account
     */
    public function store(StoreAccountRequest $request, Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create accounts.');
        }

        $validated = $request->validated();

        // If parent_id is set, ensure it belongs to this drive
        if (!empty($validated['parent_id'])) {
            $parent = Account::find($validated['parent_id']);
            if (!$parent || $parent->drive_id !== $drive->id) {
                return redirect()->back()
                    ->withErrors(['parent_id' => 'Invalid parent account.'])
                    ->withInput();
            }
        }

        $drive->accounts()->create(array_merge($validated, [
            'created_by' => Auth::id(),
            'is_active' => $request->has('is_active'),
            'is_system' => false,
        ]));

        return redirect()->route('drives.bookkeeper.accounts.index', $drive)
            ->with('success', 'Account created successfully!');
    }

    /**
     * Display the specified account
     */
    public function show(Drive $drive, Account $account)
    {
        $this->authorize('view', $drive);

        if ($account->drive_id !== $drive->id) {
            abort(404);
        }

        $account->load(['parent', 'children', 'transactions' => function ($query) {
            $query->orderBy('date', 'desc')->limit(50);
        }]);

        return view('bookkeeper.accounts.show', compact('drive', 'account'));
    }

    /**
     * Show the form for editing the specified account
     */
    public function edit(Drive $drive, Account $account)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit accounts.');
        }

        if ($account->drive_id !== $drive->id) {
            abort(404);
        }

        $parentAccounts = $drive->accounts()
            ->whereNull('parent_id')
            ->where('id', '!=', $account->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('bookkeeper.accounts.edit', compact('drive', 'account', 'parentAccounts'));
    }

    /**
     * Update the specified account
     */
    public function update(UpdateAccountRequest $request, Drive $drive, Account $account)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit accounts.');
        }

        if ($account->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validated();

        // If parent_id is set, ensure it belongs to this drive and is not the account itself
        if (!empty($validated['parent_id'])) {
            $parent = Account::find($validated['parent_id']);
            if (!$parent || $parent->drive_id !== $drive->id || $parent->id === $account->id) {
                return redirect()->back()
                    ->withErrors(['parent_id' => 'Invalid parent account.'])
                    ->withInput();
            }
        }

        $account->update(array_merge($validated, [
            'is_active' => $request->has('is_active'),
        ]));

        return redirect()->route('drives.bookkeeper.accounts.index', $drive)
            ->with('success', 'Account updated successfully!');
    }

    /**
     * Remove the specified account
     */
    public function destroy(Drive $drive, Account $account)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to delete
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot delete accounts.');
        }

        if ($account->drive_id !== $drive->id) {
            abort(404);
        }

        // Prevent deletion if account has transactions
        if ($account->transactions()->exists()) {
            return redirect()->back()
                ->withErrors(['error' => 'Cannot delete account with existing transactions.']);
        }

        // Prevent deletion if account has children
        if ($account->children()->exists()) {
            return redirect()->back()
                ->withErrors(['error' => 'Cannot delete account with child accounts.']);
        }

        $account->delete();

        return redirect()->route('drives.bookkeeper.accounts.index', $drive)
            ->with('success', 'Account deleted successfully!');
    }
}
