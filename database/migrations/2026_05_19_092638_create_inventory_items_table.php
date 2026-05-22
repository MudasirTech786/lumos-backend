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
        Schema::create('inventory_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('category_id')
                ->constrained('inventory_categories')
                ->cascadeOnDelete();

            /*
    BASIC INFO
    */

            $table->string('name');

            $table->string('model')->nullable();

            $table->string('sku')
                ->nullable()
                ->unique();

            /*
    SERIAL TRACKING
    */

            $table->string('serial_number')
                ->nullable();

            $table->boolean('track_serial')
                ->default(false);

            /*
    INVENTORY TYPE
    */

            $table->enum('type', [
                'asset',
                'consumable'
            ])->default('asset');

            /*
    STOCK
    */

            $table->integer('quantity')
                ->default(0);

            $table->integer('minimum_quantity')
                ->default(0);

            /*
    PURCHASE INFO
    */

            $table->string('purchased_from')
                ->nullable();

            $table->date('purchase_date')
                ->nullable();

            $table->decimal('purchase_price', 12, 2)
                ->nullable();

            /*
    WARRANTY
    */

            $table->date('warranty_expiry')
                ->nullable();

            /*
    RENTAL FLAGS
    */

            $table->boolean('is_rentable')
                ->default(true);

            $table->boolean('is_returnable')
                ->default(true);

            /*
    STATUS
    */

            $table->enum('status', [
                'available',
                'maintenance',
                'damaged',
                'retired'
            ])->default('available');

            /*
    OPTIONAL
    */

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
        Schema::dropIfExists('inventory_items');
    }
};
