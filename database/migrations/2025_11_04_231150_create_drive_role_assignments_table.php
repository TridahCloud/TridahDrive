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
        Schema::create('drive_role_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drive_id')->constrained()->onDelete('cascade');
            $table->foreignId('drive_role_id')->constrained()->onDelete('cascade');
            $table->morphs('assignable'); // Can assign to Person or User
            $table->timestamps();
            
            // Ensure a person/user can only have one role per drive
            $table->unique(['drive_id', 'assignable_type', 'assignable_id'], 'drive_role_assign_unique');
            $table->index(['drive_role_id', 'assignable_type', 'assignable_id'], 'drive_role_assignable_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drive_role_assignments');
    }
};
