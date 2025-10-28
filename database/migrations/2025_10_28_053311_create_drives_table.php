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
        Schema::create('drives', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['personal', 'shared'])->default('personal');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('color')->nullable(); // For UI customization
            $table->string('icon')->nullable(); // Icon identifier
            $table->json('settings')->nullable(); // Drive-specific settings
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['owner_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drives');
    }
};
