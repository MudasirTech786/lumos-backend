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
        Schema::create('inventory_assets', function (Blueprint $table) {

            $table->id();

            $table->foreignId('inventory_item_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('asset_code')->unique();

            $table->uuid('qr_uuid')->unique();

            $table->string('qr_image_path')->nullable();

            $table->string('serial_number')->nullable();

            $table->enum('status', [
                'available',
                'allocated',
                'in_use',
                'returned',
                'damaged',
                'under_repair',
                'written_off',
            ])->default('available');

            $table->timestamp('last_scanned_at')
                ->nullable();

            $table->foreignId('last_scanned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

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
        Schema::dropIfExists('inventory_assets');
    }
};
