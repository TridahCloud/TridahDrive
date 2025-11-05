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
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drive_id')->constrained()->onDelete('cascade');
            $table->foreignId('people_manager_profile_id')->nullable()->constrained('people_manager_profiles')->onDelete('set null');
            
            // Basic information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->date('date_of_birth')->nullable();
            
            // Employment information
            $table->enum('type', ['employee', 'contractor', 'volunteer'])->default('employee');
            $table->string('employee_id')->nullable()->comment('Employee ID number');
            $table->string('job_title')->nullable();
            $table->string('department')->nullable();
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'terminated', 'on_leave'])->default('active');
            
            // Payroll information
            $table->enum('pay_type', ['hourly', 'salary', 'contract', 'volunteer'])->default('hourly');
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('salary_amount', 10, 2)->nullable();
            $table->enum('salary_frequency', ['weekly', 'biweekly', 'monthly', 'annually'])->nullable();
            $table->string('pay_frequency')->default('biweekly');
            
            // Tax information
            $table->string('tax_id')->nullable()->comment('SSN/Tax ID');
            $table->string('tax_filing_status')->nullable();
            $table->integer('tax_exemptions')->default(0);
            
            // Emergency contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            
            // Additional information
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['drive_id', 'status']);
            $table->index(['drive_id', 'type']);
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
