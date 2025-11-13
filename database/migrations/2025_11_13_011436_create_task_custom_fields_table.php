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
        Schema::create('task_custom_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->enum('type', ['text', 'number', 'date', 'select', 'checkbox', 'textarea'])->default('text');
            $table->json('options')->nullable(); // For select type
            $table->boolean('required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        Schema::create('task_custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('field_definition_id')->constrained('task_custom_field_definitions')->onDelete('cascade');
            $table->text('value')->nullable();
            $table->timestamps();
            
            $table->unique(['task_id', 'field_definition_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_custom_field_values');
        Schema::dropIfExists('task_custom_field_definitions');
    }
};
