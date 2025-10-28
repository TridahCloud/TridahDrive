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
        Schema::table('invoice_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_profiles', 'accent_color')) {
                $table->string('accent_color', 7)->default('#31d8b2')->after('bank_account_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_profiles', 'accent_color')) {
                $table->dropColumn('accent_color');
            }
        });
    }
};
