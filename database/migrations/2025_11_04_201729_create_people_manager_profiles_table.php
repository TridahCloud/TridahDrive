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
        Schema::create('people_manager_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drive_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Main Office", "Remote Team"
            $table->boolean('is_default')->default(false);
            
            // Organization details
            $table->string('organization_name')->nullable();
            $table->text('organization_address')->nullable();
            $table->string('organization_phone')->nullable();
            $table->string('organization_email')->nullable();
            
            // Payroll settings
            $table->string('default_pay_frequency')->default('biweekly')->comment('weekly, biweekly, monthly, custom');
            $table->decimal('default_overtime_threshold', 8, 2)->default(40.00)->comment('Hours per week for overtime');
            $table->decimal('default_overtime_multiplier', 4, 2)->default(1.5)->comment('Overtime rate multiplier');
            
            // Settings and customizations
            $table->json('settings')->nullable();
            $table->string('accent_color', 7)->default('#31d8b2')->comment('Hex color code for accent');
            
            $table->timestamps();
            
            $table->index(['drive_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people_manager_profiles');
    }
};
