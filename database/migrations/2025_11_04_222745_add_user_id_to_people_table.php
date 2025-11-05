<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('people', 'user_id')) {
            Schema::table('people', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('drive_id')->constrained()->onDelete('set null');
            });
            
            // Add unique constraint using raw SQL to handle NULLs properly
            // This ensures one user can only be linked to one person per drive
            // Multiple NULLs are allowed (people without linked users)
            try {
                // Check if index already exists
                $indexExists = DB::select("SHOW INDEXES FROM people WHERE Key_name = 'people_drive_user_unique'");
                if (empty($indexExists)) {
                    DB::statement('ALTER TABLE people ADD UNIQUE INDEX people_drive_user_unique (drive_id, user_id)');
                }
            } catch (\Exception $e) {
                // Index might already exist or there's another issue, log but continue
                Log::info('Migration: Could not add unique index: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex('people_drive_user_unique');
            $table->dropColumn('user_id');
        });
    }
};
