<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_asset_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('shoot_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('allocated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('allocated_at');

            $table->timestamp('returned_at')
                ->nullable();

            $table->enum('status', [
                'allocated',
                'returned',
            ])->default('allocated');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'inventory_asset_id',
                'status'
            ]);

            $table->index([
                'shoot_id',
                'status'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_allocations');
    }
};