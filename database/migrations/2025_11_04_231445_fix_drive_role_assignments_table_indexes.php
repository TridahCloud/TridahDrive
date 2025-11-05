<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if table exists
        if (!Schema::hasTable('drive_role_assignments')) {
            return;
        }
        
        // Drop existing unique index if it exists (with wrong name)
        try {
            DB::statement('ALTER TABLE drive_role_assignments DROP INDEX drive_role_assignments_drive_id_assignable_type_assignable_id_unique');
        } catch (\Exception $e) {
            // Index doesn't exist or has different name, continue
        }
        
        // Add unique index with shorter name
        Schema::table('drive_role_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('drive_role_assignments', 'assignable_type')) {
                // Table was created incorrectly, need to fix it
                $table->string('assignable_type')->after('drive_role_id');
                $table->unsignedBigInteger('assignable_id')->after('assignable_type');
            }
        });
        
        // Add unique constraint
        try {
            DB::statement('ALTER TABLE drive_role_assignments ADD UNIQUE INDEX drive_role_assign_unique (drive_id, assignable_type, assignable_id)');
        } catch (\Exception $e) {
            // Index might already exist
        }
        
        // Add regular index
        try {
            DB::statement('ALTER TABLE drive_role_assignments ADD INDEX drive_role_assignable_idx (drive_role_id, assignable_type, assignable_id)');
        } catch (\Exception $e) {
            // Index might already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to rollback
    }
};
