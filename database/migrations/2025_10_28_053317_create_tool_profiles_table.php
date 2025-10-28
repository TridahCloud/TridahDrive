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
        Schema::create('tool_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drive_id')->constrained('drives')->onDelete('cascade');
            $table->string('tool_type'); // e.g., 'invoice', 'bookkeeper'
            $table->string('name'); // Profile name (e.g., "Company Profile", "Client Profile")
            $table->json('settings'); // Tool-specific profile settings
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->index(['drive_id', 'tool_type']);
            $table->index(['drive_id', 'tool_type', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tool_profiles');
    }
};
