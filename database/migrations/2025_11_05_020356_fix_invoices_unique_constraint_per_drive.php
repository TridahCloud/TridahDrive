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
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the existing global unique constraint on invoice_number
            $table->dropUnique(['invoice_number']);
            
            // Add a composite unique constraint on drive_id and invoice_number
            // This ensures invoice numbers are unique per drive, not globally
            $table->unique(['drive_id', 'invoice_number'], 'invoices_drive_invoice_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('invoices_drive_invoice_number_unique');
            
            // Restore the global unique constraint (if needed)
            $table->unique('invoice_number');
        });
    }
};
