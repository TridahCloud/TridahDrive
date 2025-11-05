<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payroll_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drive_id')->constrained()->onDelete('cascade');
            $table->foreignId('people_manager_profile_id')->nullable()->constrained('people_manager_profiles')->onDelete('set null');
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            
            // Payroll period
            $table->string('payroll_period')->comment('e.g., "2025-01-01 to 2025-01-15" or "2025-01"');
            $table->date('period_start_date');
            $table->date('period_end_date');
            $table->date('pay_date');
            
            // Hours summary
            $table->decimal('regular_hours', 5, 2)->default(0);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->decimal('total_hours', 5, 2)->default(0);
            
            // Earnings
            $table->decimal('regular_pay', 10, 2)->default(0);
            $table->decimal('overtime_pay', 10, 2)->default(0);
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('commission', 10, 2)->default(0);
            $table->decimal('gross_pay', 10, 2)->default(0);
            
            // Deductions
            $table->decimal('federal_tax', 10, 2)->default(0);
            $table->decimal('state_tax', 10, 2)->default(0);
            $table->decimal('local_tax', 10, 2)->default(0);
            $table->decimal('social_security', 10, 2)->default(0);
            $table->decimal('medicare', 10, 2)->default(0);
            $table->decimal('retirement_contribution', 10, 2)->default(0);
            $table->decimal('health_insurance', 10, 2)->default(0);
            $table->decimal('other_deductions', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            
            // Net pay
            $table->decimal('net_pay', 10, 2)->default(0);
            
            // Payment method
            $table->enum('payment_method', ['direct_deposit', 'check', 'cash', 'other'])->default('direct_deposit');
            $table->string('payment_reference')->nullable()->comment('Check number, transaction ID, etc.');
            
            // Status and sync
            $table->enum('status', ['draft', 'pending', 'processed', 'paid', 'cancelled'])->default('draft');
            $table->foreignId('book_transaction_id')->nullable()->constrained('book_transactions')->onDelete('set null');
            $table->boolean('synced_to_bookkeeper')->default(false);
            $table->dateTime('synced_at')->nullable();
            
            // Additional information
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['drive_id', 'pay_date']);
            $table->index(['person_id', 'pay_date']);
            $table->index('status');
            $table->index('synced_to_bookkeeper');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_entries');
    }
};
