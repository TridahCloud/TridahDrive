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
            if (!Schema::hasColumn('drives', 'timezone')) {
                $table->string('timezone', 50)->nullable()->after('currency');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drives', function (Blueprint $table) {
            if (Schema::hasColumn('drives', 'timezone')) {
                $table->dropColumn('timezone');
            }
        });
    }
};
