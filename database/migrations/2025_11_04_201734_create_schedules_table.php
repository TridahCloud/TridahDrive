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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drive_id')->constrained()->onDelete('cascade');
            $table->foreignId('people_manager_profile_id')->nullable()->constrained('people_manager_profiles')->onDelete('set null');
            $table->foreignId('person_id')->nullable()->constrained('people')->onDelete('cascade');
            
            // Schedule details
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['one_time', 'recurring', 'template'])->default('one_time');
            
            // Date and time
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('timezone')->default('UTC');
            
            // Recurrence (for recurring schedules)
            $table->string('recurrence_pattern')->nullable()->comment('daily, weekly, biweekly, monthly, custom');
            $table->json('recurrence_days')->nullable()->comment('Days of week: [1,3,5] for Mon,Wed,Fri');
            $table->integer('recurrence_interval')->nullable()->comment('Every N days/weeks/months');
            $table->date('recurrence_end_date')->nullable();
            $table->integer('recurrence_count')->nullable();
            
            // Status
            $table->enum('status', ['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            
            // Break times
            $table->integer('break_minutes')->default(0)->comment('Total break time in minutes');
            $table->decimal('total_hours', 5, 2)->nullable()->comment('Calculated total hours');
            
            // Location
            $table->string('location')->nullable();
            
            // Additional information
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['drive_id', 'start_date']);
            $table->index(['person_id', 'start_date']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
