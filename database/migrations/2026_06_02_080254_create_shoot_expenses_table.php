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
        Schema::create(
            'shoot_expenses',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId('shoot_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('category');

                $table->string('description')
                    ->nullable();

                $table->decimal(
                    'amount',
                    12,
                    2
                );

                $table->string('receipt')
                    ->nullable();

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users');

                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shoot_expenses');
    }
};
