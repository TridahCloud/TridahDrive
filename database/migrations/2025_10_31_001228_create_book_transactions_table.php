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
        Schema::create('book_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drive_id')->constrained()->onDelete('cascade');
            $table->string('transaction_number', 50);
            $table->date('date');
            $table->text('description');
            $table->string('reference', 100)->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['income', 'expense', 'transfer', 'adjustment']);
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('payee', 255)->nullable();
            $table->enum('payment_method', ['cash', 'check', 'credit_card', 'debit_card', 'bank_transfer', 'other'])->nullable();
            $table->enum('status', ['pending', 'cleared', 'reconciled'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Unique constraint: transaction_number must be unique per drive
            $table->unique(['drive_id', 'transaction_number']);
            $table->index(['date', 'type', 'status']);
            $table->index('account_id');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_transactions');
    }
};
