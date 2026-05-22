<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'write_offs',

            function (Blueprint $table) {

                $table->id();

                /* ===================================== */
                /* RELATIONS */
                /* ===================================== */

                $table->foreignId(
                    'inventory_item_id'
                )
                ->constrained()
                ->cascadeOnDelete();

                $table->foreignId(
                    'damage_report_id'
                )
                ->nullable()
                ->constrained()
                ->nullOnDelete();

                $table->foreignId(
                    'approved_by'
                )
                ->constrained('users')
                ->cascadeOnDelete();

                /* ===================================== */
                /* WRITE OFF INFO */
                /* ===================================== */

                $table->string(
                    'reason'
                );

                $table->text(
                    'notes'
                )->nullable();

                /* ===================================== */
                /* ACCOUNTING */
                /* ===================================== */

                $table->decimal(
                    'estimated_loss_value',
                    12,
                    2
                )->nullable();

                /* ===================================== */
                /* DATE */
                /* ===================================== */

                $table->timestamp(
                    'written_off_at'
                )->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'write_offs'
        );
    }
};