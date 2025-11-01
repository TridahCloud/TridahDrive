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
        Schema::table('recurring_transactions', function (Blueprint $table) {
            // Interval for "every N days/weeks/months/years" (e.g., every 2 weeks)
            $table->integer('frequency_interval')->default(1)->after('frequency');
            
            // Specific day of week (0=Sunday, 1=Monday, ..., 6=Saturday, null = no specific day)
            $table->integer('frequency_day_of_week')->nullable()->after('frequency_interval');
            
            // Specific day of month (1-31, null = no specific day)
            $table->integer('frequency_day_of_month')->nullable()->after('frequency_day_of_week');
            
            // Week of month (1=first, 2=second, 3=third, 4=fourth, 5=last, null = no specific week)
            $table->integer('frequency_week_of_month')->nullable()->after('frequency_day_of_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recurring_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'frequency_interval',
                'frequency_day_of_week',
                'frequency_day_of_month',
                'frequency_week_of_month',
            ]);
        });
    }
};
