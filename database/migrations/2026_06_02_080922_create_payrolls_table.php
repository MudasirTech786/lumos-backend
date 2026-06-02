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
        Schema::create('payrolls', function (Blueprint $table) {

            $table->id();

            $table->string('reference')->unique();

            $table->enum('type', [
                'crew',
                'employee'
            ]);

            $table->date('period_start');

            $table->date('period_end');

            $table->decimal('gross_amount', 12, 2)->default(0);

            $table->decimal('deduction_amount', 12, 2)->default(0);

            $table->decimal('bonus_amount', 12, 2)->default(0);

            $table->decimal('net_amount', 12, 2)->default(0);

            $table->enum('status', [
                'draft',
                'approved',
                'paid'
            ])->default('draft');

            $table->foreignId('generated_by')
                ->nullable()
                ->constrained('users');

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users');

            $table->timestamp('paid_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
