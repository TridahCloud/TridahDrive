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
        Schema::create('drive_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drive_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_role_id')->nullable()->constrained('drive_roles')->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_inherited')->default(false)->comment('Whether this role inherits from parent drive');
            $table->boolean('override_permissions')->default(false)->comment('Whether this role overrides parent permissions');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['drive_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drive_roles');
    }
};
