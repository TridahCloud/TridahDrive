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
        Schema::create('drive_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drive_role_id')->constrained()->onDelete('cascade');
            $table->string('permission_key'); // e.g., 'mytime.view_own_schedules', 'project.view_all', 'project.view_specific'
            $table->json('permission_value')->nullable(); // For specific IDs or complex permissions: {"project_ids": [1,2,3]} or true/false
            $table->timestamps();
            
            $table->unique(['drive_role_id', 'permission_key']);
            $table->index('permission_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drive_role_permissions');
    }
};
