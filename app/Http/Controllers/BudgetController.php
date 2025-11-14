<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Models\Drive;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    /**
     * Display a listing of budgets for the drive
     */
    public function index(Drive $drive)
    {
        $this->authorize('view', $drive);

        $budgets = $drive->budgets()
            ->with(['category', 'creator', 'transactions'])
            ->orderBy('start_date', 'desc')
            ->orderBy('name')
            ->get();

        return view('bookkeeper.budgets.index', compact('drive', 'budgets'));
    }

    /**
     * Show the form for creating a new budget
     */
    public function create(Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create budgets.');
        }

        $categories = $drive->categories()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('bookkeeper.budgets.create', compact('drive', 'categories'));
    }

    /**
     * Store a newly created budget
     */
    public function store(StoreBudgetRequest $request, Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create budgets.');
        }

        $validated = $request->validated();

        // Verify category belongs to drive if provided
        if (!empty($validated['category_id'])) {
            $category = Category::findOrFail($validated['category_id']);
            if ($category->drive_id !== $drive->id) {
                return redirect()->back()
                    ->withErrors(['category_id' => 'Invalid category.'])
                    ->withInput();
            }
        }

        $budget = $drive->budgets()->create(array_merge($validated, [
            'created_by' => Auth::id(),
            'is_active' => $request->has('is_active'),
        ]));

        return redirect()->route('drives.bookkeeper.budgets.show', [$drive, $budget])
            ->with('success', 'Budget created successfully!');
    }

    /**
     * Display the specified budget
     */
    public function show(Drive $drive, Budget $budget)
    {
        $this->authorize('view', $drive);

        if ($budget->drive_id !== $drive->id) {
            abort(404);
        }

        $budget->load(['category', 'creator', 'transactions' => function ($query) {
            $query->orderBy('date', 'desc')->limit(100);
        }, 'transactions.account', 'transactions.category']);

        return view('bookkeeper.budgets.show', compact('drive', 'budget'));
    }

    /**
     * Show the form for editing the specified budget
     */
    public function edit(Drive $drive, Budget $budget)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit budgets.');
        }

        if ($budget->drive_id !== $drive->id) {
            abort(404);
        }

        $categories = $drive->categories()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('bookkeeper.budgets.edit', compact('drive', 'budget', 'categories'));
    }

    /**
     * Update the specified budget
     */
    public function update(UpdateBudgetRequest $request, Drive $drive, Budget $budget)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit budgets.');
        }

        if ($budget->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validated();

        // Verify category belongs to drive if provided
        if (!empty($validated['category_id'])) {
            $category = Category::findOrFail($validated['category_id']);
            if ($category->drive_id !== $drive->id) {
                return redirect()->back()
                    ->withErrors(['category_id' => 'Invalid category.'])
                    ->withInput();
            }
        }

        $budget->update(array_merge($validated, [
            'is_active' => $request->has('is_active'),
        ]));

        return redirect()->route('drives.bookkeeper.budgets.show', [$drive, $budget])
            ->with('success', 'Budget updated successfully!');
    }

    /**
     * Remove the specified budget
     */
    public function destroy(Drive $drive, Budget $budget)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to delete
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot delete budgets.');
        }

        if ($budget->drive_id !== $drive->id) {
            abort(404);
        }

        // Remove budget from transactions (set to null)
        $budget->transactions()->update(['budget_id' => null]);

        $budget->delete();

        return redirect()->route('drives.bookkeeper.budgets.index', $drive)
            ->with('success', 'Budget deleted successfully!');
    }
}

