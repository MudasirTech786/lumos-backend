<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shoot_id')
                ->constrained('shoots')
                ->cascadeOnDelete();

            $table->string('invoice_number')->unique();

            $table->string('title')->nullable();

            $table->date('issue_date');

            $table->date('due_date')->nullable();

            $table->decimal('subtotal', 15, 2)->default(0);

            $table->decimal('tax_percentage', 8, 2)->default(0);

            $table->decimal('tax_amount', 15, 2)->default(0);

            $table->decimal('discount_amount', 15, 2)->default(0);

            $table->decimal('total_amount', 15, 2)->default(0);

            $table->decimal('paid_amount', 15, 2)->default(0);

            $table->decimal('balance_due', 15, 2)->default(0);

            $table->enum('status', [
                'draft',
                'sent',
                'partially_paid',
                'paid',
                'overdue',
                'cancelled'
            ])->default('draft');

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('shoot_id');
            $table->index('status');
            $table->index('invoice_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_invoices');
    }
};