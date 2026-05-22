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
        Schema::create('inventory_usages', function (Blueprint $table) {

            $table->id();

            // =====================================
            // INVENTORY ITEM
            // =====================================

            $table->foreignId('inventory_item_id')
                ->constrained('inventory_items')
                ->cascadeOnDelete();

            // =====================================
            // USAGE TYPE
            // =====================================

            $table->enum('usage_type', [

                'shoot',
                'rental',
                'internal',
                'maintenance',

            ])->default('shoot');

            // =====================================
            // OPTIONAL RELATIONS
            // =====================================

            $table->foreignId('shoot_id')
                ->nullable()
                ->constrained('shoots')
                ->nullOnDelete();

            // Future Rental Orders
            $table->unsignedBigInteger('rental_order_id')
                ->nullable();

            // Assigned User / Department
            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Creator
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // =====================================
            // QUANTITIES
            // =====================================

            $table->integer('quantity')
                ->default(1);

            $table->integer('returned_quantity')
                ->default(0);

            $table->integer('damaged_quantity')
                ->default(0);

            $table->integer(
                'lost_quantity'
            )->default(0);

            // =====================================
            // STATUS
            // =====================================

            $table->enum('status', [

                'reserved',
                'checked_out',
                'in_use',
                'partially_returned',
                'returned',
                'damaged',
                'lost',

            ])->default('reserved');

            // =====================================
            // DATES
            // =====================================

            $table->timestamp('reserved_at')
                ->nullable();

            $table->timestamp('checked_out_at')
                ->nullable();

            $table->timestamp('returned_at')
                ->nullable();

            // =====================================
            // NOTES
            // =====================================

            $table->text('notes')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_usages');
    }
};
