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
        Schema::create('invoice_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drive_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Personal", "Business"
            $table->boolean('is_default')->default(false);
            
            // Company details
            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_website')->nullable();
            $table->string('logo_url')->nullable();
            
            // Invoice customization
            $table->json('customizations')->nullable(); // Show/hide elements
            
            // Invoice numbering
            $table->string('invoice_prefix')->default('INV');
            $table->integer('next_invoice_number')->default(1);
            
            // Payment info
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_routing_label')->default('Routing Number');
            $table->string('bank_routing_number')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('accent_color', 7)->default('#31d8b2')->comment('Hex color code for invoice accent');
            
            $table->timestamps();
            
            $table->index(['drive_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_profiles');
    }
};
