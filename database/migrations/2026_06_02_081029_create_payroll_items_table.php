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
        Schema::create('payroll_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('payroll_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('shoot_id')
                ->nullable()
                ->constrained();

            $table->enum('person_type', [
                'crew',
                'employee'
            ]);

            $table->unsignedBigInteger('person_id');

            $table->string('description');

            $table->integer('quantity')
                ->default(1);

            $table->decimal('rate', 12, 2);

            $table->decimal('gross_amount', 12, 2);

            $table->decimal('deduction_amount', 12, 2)
                ->default(0);

            $table->decimal('bonus_amount', 12, 2)
                ->default(0);

            $table->decimal('net_amount', 12, 2);

            $table->boolean('is_paid')
                ->default(false);

            $table->timestamp('paid_at')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};
