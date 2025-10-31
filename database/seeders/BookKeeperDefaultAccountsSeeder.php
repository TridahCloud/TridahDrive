<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Drive;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class BookKeeperDefaultAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates default chart of accounts for a drive
     */
    public function run(Drive $drive, $userId = null): void
    {
        $userId = $userId ?? Auth::id();

        // Standard Chart of Accounts Structure
        
        // ASSETS (1000-1999)
        $assets = Account::create([
            'drive_id' => $drive->id,
            'account_code' => '1000',
            'name' => 'Assets',
            'type' => 'asset',
            'description' => 'All assets',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        // Current Assets
        Account::create([
            'drive_id' => $drive->id,
            'parent_id' => $assets->id,
            'account_code' => '1100',
            'name' => 'Current Assets',
            'type' => 'asset',
            'subtype' => 'Current',
            'description' => 'Assets expected to be converted to cash within one year',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Account::create([
            'drive_id' => $drive->id,
            'parent_id' => $assets->id,
            'account_code' => '1110',
            'name' => 'Cash',
            'type' => 'asset',
            'subtype' => 'Current',
            'description' => 'Cash and cash equivalents',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Account::create([
            'drive_id' => $drive->id,
            'parent_id' => $assets->id,
            'account_code' => '1120',
            'name' => 'Accounts Receivable',
            'type' => 'asset',
            'subtype' => 'Current',
            'description' => 'Money owed by customers',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Account::create([
            'drive_id' => $drive->id,
            'parent_id' => $assets->id,
            'account_code' => '1200',
            'name' => 'Fixed Assets',
            'type' => 'asset',
            'subtype' => 'Fixed',
            'description' => 'Long-term assets',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        // LIABILITIES (2000-2999)
        $liabilities = Account::create([
            'drive_id' => $drive->id,
            'account_code' => '2000',
            'name' => 'Liabilities',
            'type' => 'liability',
            'description' => 'All liabilities',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Account::create([
            'drive_id' => $drive->id,
            'parent_id' => $liabilities->id,
            'account_code' => '2100',
            'name' => 'Accounts Payable',
            'type' => 'liability',
            'subtype' => 'Current',
            'description' => 'Money owed to suppliers',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Account::create([
            'drive_id' => $drive->id,
            'parent_id' => $liabilities->id,
            'account_code' => '2200',
            'name' => 'Long-term Debt',
            'type' => 'liability',
            'subtype' => 'Long-term',
            'description' => 'Long-term loans and debts',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        // EQUITY (3000-3999)
        $equity = Account::create([
            'drive_id' => $drive->id,
            'account_code' => '3000',
            'name' => 'Equity',
            'type' => 'equity',
            'description' => 'Owner\'s equity',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Account::create([
            'drive_id' => $drive->id,
            'parent_id' => $equity->id,
            'account_code' => '3100',
            'name' => 'Owner\'s Capital',
            'type' => 'equity',
            'description' => 'Initial capital investment',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Account::create([
            'drive_id' => $drive->id,
            'parent_id' => $equity->id,
            'account_code' => '3200',
            'name' => 'Retained Earnings',
            'type' => 'equity',
            'description' => 'Accumulated profits',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        // REVENUE (4000-4999)
        $revenue = Account::create([
            'drive_id' => $drive->id,
            'account_code' => '4000',
            'name' => 'Revenue',
            'type' => 'revenue',
            'description' => 'All revenue',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Account::create([
            'drive_id' => $drive->id,
            'parent_id' => $revenue->id,
            'account_code' => '4100',
            'name' => 'Sales Revenue',
            'type' => 'revenue',
            'description' => 'Revenue from sales',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Account::create([
            'drive_id' => $drive->id,
            'parent_id' => $revenue->id,
            'account_code' => '4200',
            'name' => 'Service Revenue',
            'type' => 'revenue',
            'description' => 'Revenue from services',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        // EXPENSES (5000-5999)
        $expenses = Account::create([
            'drive_id' => $drive->id,
            'account_code' => '5000',
            'name' => 'Expenses',
            'type' => 'expense',
            'description' => 'All expenses',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Account::create([
            'drive_id' => $drive->id,
            'parent_id' => $expenses->id,
            'account_code' => '5100',
            'name' => 'Cost of Goods Sold',
            'type' => 'expense',
            'description' => 'Direct costs of producing goods or services',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Account::create([
            'drive_id' => $drive->id,
            'parent_id' => $expenses->id,
            'account_code' => '5200',
            'name' => 'Operating Expenses',
            'type' => 'expense',
            'description' => 'General operating expenses',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Account::create([
            'drive_id' => $drive->id,
            'parent_id' => $expenses->id,
            'account_code' => '5300',
            'name' => 'Salaries & Wages',
            'type' => 'expense',
            'description' => 'Employee salaries and wages',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Account::create([
            'drive_id' => $drive->id,
            'parent_id' => $expenses->id,
            'account_code' => '5400',
            'name' => 'Rent Expense',
            'type' => 'expense',
            'description' => 'Rent and lease expenses',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Account::create([
            'drive_id' => $drive->id,
            'parent_id' => $expenses->id,
            'account_code' => '5500',
            'name' => 'Utilities',
            'type' => 'expense',
            'description' => 'Utility bills',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);

        Account::create([
            'drive_id' => $drive->id,
            'parent_id' => $expenses->id,
            'account_code' => '5600',
            'name' => 'Marketing & Advertising',
            'type' => 'expense',
            'description' => 'Marketing and advertising expenses',
            'is_active' => true,
            'is_system' => true,
            'created_by' => $userId,
        ]);
    }
}
