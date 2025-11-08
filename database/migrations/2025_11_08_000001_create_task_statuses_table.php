<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->string('color', 9)->default('#6B7280');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
            $table->unique(['project_id', 'slug']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('task_status_id')
                ->nullable()
                ->after('parent_id')
                ->constrained('task_statuses')
                ->nullOnDelete();
        });

        // Seed default task statuses for existing projects
        $projects = DB::table('projects')->select('id')->get();

        $defaultStatuses = [
            ['slug' => 'todo', 'name' => 'To-Do', 'color' => '#6B7280', 'is_completed' => false],
            ['slug' => 'in_progress', 'name' => 'In Progress', 'color' => '#3B82F6', 'is_completed' => false],
            ['slug' => 'review', 'name' => 'Review', 'color' => '#0EA5E9', 'is_completed' => false],
            ['slug' => 'done', 'name' => 'Done', 'color' => '#10B981', 'is_completed' => true],
            ['slug' => 'blocked', 'name' => 'Blocked', 'color' => '#EF4444', 'is_completed' => false],
        ];

        $statusLookup = [];
        $now = now();

        foreach ($projects as $project) {
            $sortOrder = 0;
            foreach ($defaultStatuses as $status) {
                $statusId = DB::table('task_statuses')->insertGetId([
                    'project_id' => $project->id,
                    'name' => $status['name'],
                    'slug' => $status['slug'],
                    'color' => $status['color'],
                    'is_completed' => $status['is_completed'],
                    'sort_order' => $sortOrder,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $statusLookup[$project->id][$status['slug']] = $statusId;
                $sortOrder += 10;
            }
        }

        // Ensure any unexpected status values are captured
        $tasks = DB::table('tasks')->select('id', 'project_id', 'status')->get();
        foreach ($tasks as $task) {
            $slug = $task->status ?? 'todo';

            if (!isset($statusLookup[$task->project_id][$slug])) {
                $derivedName = Str::headline(str_replace('-', ' ', $slug));
                $statusId = DB::table('task_statuses')->insertGetId([
                    'project_id' => $task->project_id,
                    'name' => $derivedName,
                    'slug' => $slug,
                    'color' => '#6B7280',
                    'is_completed' => false,
                    'sort_order' => (count($statusLookup[$task->project_id] ?? []) + 1) * 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $statusLookup[$task->project_id][$slug] = $statusId;
            }

            DB::table('tasks')
                ->where('id', $task->id)
                ->update([
                    'task_status_id' => $statusLookup[$task->project_id][$slug],
                ]);
        }

        // Add new indexes using the task_status_id column before removing the old ones
        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['project_id', 'task_status_id', 'sort_order'], 'tasks_project_status_sort_order_index');
            $table->index(['due_date', 'task_status_id'], 'tasks_due_date_task_status_index');
        });

        // Drop old indexes relying on the enum status column
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_project_id_status_sort_order_index');
            $table->dropIndex('tasks_due_date_status_index');
        });

        // Remove the old enum column
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the status column
        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('status', ['todo', 'in_progress', 'review', 'done', 'blocked'])
                ->default('todo')
                ->after('parent_id');
        });

        // Rehydrate the status values from their related task statuses
        $statusLookup = DB::table('task_statuses')->pluck('slug', 'id');

        $tasks = DB::table('tasks')->select('id', 'task_status_id')->get();

        foreach ($tasks as $task) {
            $slug = $statusLookup[$task->task_status_id] ?? 'todo';

            DB::table('tasks')
                ->where('id', $task->id)
                ->update(['status' => $slug]);
        }

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_project_status_sort_order_index');
            $table->dropIndex('tasks_due_date_task_status_index');
            $table->dropForeign(['task_status_id']);
            $table->dropColumn('task_status_id');
            $table->index(['project_id', 'status', 'sort_order']);
            $table->index(['due_date', 'status']);
        });

        Schema::dropIfExists('task_statuses');
    }
};


