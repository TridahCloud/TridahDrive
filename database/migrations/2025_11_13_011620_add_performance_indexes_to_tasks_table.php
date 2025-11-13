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
        Schema::table('tasks', function (Blueprint $table) {
            // Add composite index for common query patterns
            $table->index(['project_id', 'task_status_id', 'deleted_at'], 'tasks_project_status_deleted_idx');
            $table->index(['project_id', 'priority', 'deleted_at'], 'tasks_project_priority_deleted_idx');
            $table->index(['owner_id', 'deleted_at'], 'tasks_owner_deleted_idx');
        });
        
        Schema::table('task_dependencies', function (Blueprint $table) {
            $table->index(['task_id', 'type'], 'task_dependencies_task_type_idx');
            $table->index(['depends_on_task_id', 'type'], 'task_dependencies_depends_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_project_status_deleted_idx');
            $table->dropIndex('tasks_project_priority_deleted_idx');
            $table->dropIndex('tasks_owner_deleted_idx');
        });
        
        Schema::table('task_dependencies', function (Blueprint $table) {
            $table->dropIndex('task_dependencies_task_type_idx');
            $table->dropIndex('task_dependencies_depends_type_idx');
        });
    }
};
