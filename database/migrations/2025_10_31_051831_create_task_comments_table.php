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
        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Comment author
            $table->text('comment');
            $table->text('comment_html')->nullable(); // HTML version with @username links
            $table->json('mentioned_users')->nullable(); // Array of user IDs mentioned with @username
            $table->foreignId('parent_id')->nullable()->constrained('task_comments')->onDelete('cascade'); // For threaded comments
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['task_id', 'created_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_comments');
    }
};
