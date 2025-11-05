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
        Schema::create('time_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drive_id')->constrained()->onDelete('cascade');
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->onDelete('set null');
            
            // Date and time tracking
            $table->date('work_date');
            $table->dateTime('clock_in')->nullable();
            $table->dateTime('clock_out')->nullable();
            
            // Hours calculation
            $table->decimal('regular_hours', 5, 2)->default(0)->comment('Regular hours worked');
            $table->decimal('overtime_hours', 5, 2)->default(0)->comment('Overtime hours worked');
            $table->decimal('break_hours', 5, 2)->default(0)->comment('Break time in hours');
            $table->decimal('total_hours', 5, 2)->default(0)->comment('Total hours (regular + overtime)');
            
            // Pay calculation
            $table->decimal('regular_pay', 10, 2)->default(0);
            $table->decimal('overtime_pay', 10, 2)->default(0);
            $table->decimal('total_pay', 10, 2)->default(0);
            
            // Status
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Location tracking
            $table->string('clock_in_location')->nullable();
            $table->string('clock_out_location')->nullable();
            $table->decimal('clock_in_latitude', 10, 8)->nullable();
            $table->decimal('clock_in_longitude', 11, 8)->nullable();
            $table->decimal('clock_out_latitude', 10, 8)->nullable();
            $table->decimal('clock_out_longitude', 11, 8)->nullable();
            
            // Additional information
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['drive_id', 'work_date']);
            $table->index(['person_id', 'work_date']);
            $table->index('status');
            $table->index(['person_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_logs');
    }
};
