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
        Schema::create('user_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('drive_id')->nullable()->constrained()->onDelete('cascade'); // Drive-specific or global
            
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit')->default('items');
            $table->decimal('default_price', 10, 2)->default(0);
            
            $table->timestamps();
            
            $table->index(['user_id', 'drive_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_items');
    }
};
