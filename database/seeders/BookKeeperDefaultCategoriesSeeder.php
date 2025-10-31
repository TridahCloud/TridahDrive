<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Drive;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class BookKeeperDefaultCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates default categories for a drive
     */
    public function run(Drive $drive, $userId = null): void
    {
        $userId = $userId ?? Auth::id();

        // Income Categories
        $income = Category::create([
            'drive_id' => $drive->id,
            'name' => 'Income',
            'description' => 'All income categories',
            'color' => '#10B981', // Green
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Category::create([
            'drive_id' => $drive->id,
            'parent_id' => $income->id,
            'name' => 'Product Sales',
            'description' => 'Revenue from product sales',
            'color' => '#10B981',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Category::create([
            'drive_id' => $drive->id,
            'parent_id' => $income->id,
            'name' => 'Service Revenue',
            'description' => 'Revenue from services',
            'color' => '#10B981',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Category::create([
            'drive_id' => $drive->id,
            'parent_id' => $income->id,
            'name' => 'Other Income',
            'description' => 'Other sources of income',
            'color' => '#10B981',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        // Expense Categories
        $expenses = Category::create([
            'drive_id' => $drive->id,
            'name' => 'Expenses',
            'description' => 'All expense categories',
            'color' => '#EF4444', // Red
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Category::create([
            'drive_id' => $drive->id,
            'parent_id' => $expenses->id,
            'name' => 'Office Supplies',
            'description' => 'Office supplies and materials',
            'color' => '#EF4444',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Category::create([
            'drive_id' => $drive->id,
            'parent_id' => $expenses->id,
            'name' => 'Travel',
            'description' => 'Travel and transportation expenses',
            'color' => '#EF4444',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Category::create([
            'drive_id' => $drive->id,
            'parent_id' => $expenses->id,
            'name' => 'Meals & Entertainment',
            'description' => 'Business meals and entertainment',
            'color' => '#EF4444',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Category::create([
            'drive_id' => $drive->id,
            'parent_id' => $expenses->id,
            'name' => 'Professional Services',
            'description' => 'Legal, accounting, consulting fees',
            'color' => '#EF4444',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Category::create([
            'drive_id' => $drive->id,
            'parent_id' => $expenses->id,
            'name' => 'Technology',
            'description' => 'Software, hardware, IT services',
            'color' => '#EF4444',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Category::create([
            'drive_id' => $drive->id,
            'parent_id' => $expenses->id,
            'name' => 'Marketing',
            'description' => 'Marketing and advertising expenses',
            'color' => '#EF4444',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Category::create([
            'drive_id' => $drive->id,
            'parent_id' => $expenses->id,
            'name' => 'Utilities',
            'description' => 'Electricity, water, internet, etc.',
            'color' => '#EF4444',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Category::create([
            'drive_id' => $drive->id,
            'parent_id' => $expenses->id,
            'name' => 'Rent',
            'description' => 'Rent and lease payments',
            'color' => '#EF4444',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Category::create([
            'drive_id' => $drive->id,
            'parent_id' => $expenses->id,
            'name' => 'Other Expenses',
            'description' => 'Other miscellaneous expenses',
            'color' => '#EF4444',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);
    }
}
