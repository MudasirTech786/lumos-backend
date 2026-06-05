<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_invoice_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('production_invoices')
                ->cascadeOnDelete();

            $table->date('payment_date');

            $table->decimal('amount', 15, 2);

            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'cheque',
                'online'
            ])->default('bank_transfer');

            $table->string('reference_number')
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('invoice_id');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_invoice_payments');
    }
};