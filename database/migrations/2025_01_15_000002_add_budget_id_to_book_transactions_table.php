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
        Schema::table('book_transactions', function (Blueprint $table) {
            $table->foreignId('budget_id')->nullable()->after('category_id')->constrained('budgets')->onDelete('set null');
            $table->index('budget_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_transactions', function (Blueprint $table) {
            $table->dropForeign(['budget_id']);
            $table->dropIndex(['budget_id']);
            $table->dropColumn('budget_id');
        });
    }
};

