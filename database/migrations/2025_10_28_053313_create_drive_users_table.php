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
        Schema::create('drive_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drive_id')->constrained('drives')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['owner', 'admin', 'member', 'viewer'])->default('member');
            $table->foreignId('invited_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            
            // Ensure a user can only be in a drive once
            $table->unique(['drive_id', 'user_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drive_users');
    }
};
