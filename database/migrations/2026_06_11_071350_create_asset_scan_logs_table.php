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
        Schema::create('asset_scan_logs', function (Blueprint $table) {

            $table->id();

            $table->foreignId('inventory_asset_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('shoot_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->enum('action', [
                'lookup',
                'checkout',
                'return',
                'damage',
                'repair',
                'writeoff',
                'inspection',
                'allocated',
                'returned',
                'created',
                'status_changed'
            ]);

            $table->string('location')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_scan_logs');
    }
};
