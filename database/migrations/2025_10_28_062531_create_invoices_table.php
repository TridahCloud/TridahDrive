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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drive_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('invoice_profile_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('invoice_number')->unique();
            
            // Client details (denormalized for PDF)
            $table->string('client_name')->nullable();
            $table->text('client_address')->nullable();
            $table->string('client_email')->nullable();
            
            $table->date('issue_date');
            $table->date('due_date');
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            
            // Financials
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('drive_id');
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
