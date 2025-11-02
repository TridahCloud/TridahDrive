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
        Schema::table('drives', function (Blueprint $table) {
            $table->foreignId('parent_drive_id')->nullable()->after('owner_id')->constrained('drives')->onDelete('cascade');
            $table->index('parent_drive_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drives', function (Blueprint $table) {
            $table->dropForeign(['parent_drive_id']);
            $table->dropIndex(['parent_drive_id']);
            $table->dropColumn('parent_drive_id');
        });
    }
};
