<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Drive;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories for the drive
     */
    public function index(Drive $drive)
    {
        $this->authorize('view', $drive);

        $categories = $drive->categories()
            ->with(['parent', 'children'])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('bookkeeper.categories.index', compact('drive', 'categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create(Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create categories.');
        }

        $parentCategories = $drive->categories()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('bookkeeper.categories.create', compact('drive', 'parentCategories'));
    }

    /**
     * Store a newly created category
     */
    public function store(StoreCategoryRequest $request, Drive $drive)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to create
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot create categories.');
        }

        $validated = $request->validated();

        // If parent_id is set, ensure it belongs to this drive
        if (!empty($validated['parent_id'])) {
            $parent = Category::find($validated['parent_id']);
            if (!$parent || $parent->drive_id !== $drive->id) {
                return redirect()->back()
                    ->withErrors(['parent_id' => 'Invalid parent category.'])
                    ->withInput();
            }
        }

        $drive->categories()->create(array_merge($validated, [
            'created_by' => Auth::id(),
            'is_active' => $request->has('is_active'),
            'is_system' => false,
            'color' => $validated['color'] ?? '#3B82F6',
        ]));

        return redirect()->route('drives.bookkeeper.categories.index', $drive)
            ->with('success', 'Category created successfully!');
    }

    /**
     * Display the specified category
     */
    public function show(Drive $drive, Category $category)
    {
        $this->authorize('view', $drive);

        if ($category->drive_id !== $drive->id) {
            abort(404);
        }

        $category->load(['parent', 'children', 'transactions' => function ($query) {
            $query->orderBy('date', 'desc')->limit(50);
        }]);

        return view('bookkeeper.categories.show', compact('drive', 'category'));
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(Drive $drive, Category $category)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit categories.');
        }

        if ($category->drive_id !== $drive->id) {
            abort(404);
        }

        $parentCategories = $drive->categories()
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('bookkeeper.categories.edit', compact('drive', 'category', 'parentCategories'));
    }

    /**
     * Update the specified category
     */
    public function update(UpdateCategoryRequest $request, Drive $drive, Category $category)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to edit
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot edit categories.');
        }

        if ($category->drive_id !== $drive->id) {
            abort(404);
        }

        $validated = $request->validated();

        // If parent_id is set, ensure it belongs to this drive and is not the category itself
        if (!empty($validated['parent_id'])) {
            $parent = Category::find($validated['parent_id']);
            if (!$parent || $parent->drive_id !== $drive->id || $parent->id === $category->id) {
                return redirect()->back()
                    ->withErrors(['parent_id' => 'Invalid parent category.'])
                    ->withInput();
            }
        }

        $category->update(array_merge($validated, [
            'is_active' => $request->has('is_active'),
            'color' => $validated['color'] ?? $category->color,
        ]));

        return redirect()->route('drives.bookkeeper.categories.index', $drive)
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified category
     */
    public function destroy(Drive $drive, Category $category)
    {
        $this->authorize('view', $drive);
        
        // Check if user has permission to delete
        if (!$drive->canEdit(auth()->user())) {
            abort(403, 'Viewers cannot delete categories.');
        }

        if ($category->drive_id !== $drive->id) {
            abort(404);
        }

        // Prevent deletion if category has transactions
        if ($category->transactions()->exists()) {
            return redirect()->back()
                ->withErrors(['error' => 'Cannot delete category with existing transactions.']);
        }

        // Prevent deletion if category has children
        if ($category->children()->exists()) {
            return redirect()->back()
                ->withErrors(['error' => 'Cannot delete category with child categories.']);
        }

        $category->delete();

        return redirect()->route('drives.bookkeeper.categories.index', $drive)
            ->with('success', 'Category deleted successfully!');
    }
}
