<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_invoice_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('production_invoices')
                ->cascadeOnDelete();

            $table->string('description');

            $table->decimal('quantity', 12, 2)
                ->default(1);

            $table->decimal('unit_price', 15, 2);

            $table->decimal('line_total', 15, 2);

            $table->integer('sort_order')
                ->default(0);

            $table->timestamps();

            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_invoice_items');
    }
};