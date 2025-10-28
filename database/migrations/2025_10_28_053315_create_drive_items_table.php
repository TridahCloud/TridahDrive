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
        Schema::create('drive_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drive_id')->constrained('drives')->onDelete('cascade');
            $table->string('tool_type'); // e.g., 'invoice', 'bookkeeper', etc.
            $table->string('name');
            $table->json('data')->nullable(); // Tool-specific data
            $table->json('metadata')->nullable(); // File size, version, etc.
            $table->foreignId('created_by_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['drive_id', 'tool_type']);
            $table->index(['drive_id', 'deleted_at']);
            $table->index('created_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drive_items');
    }
};
