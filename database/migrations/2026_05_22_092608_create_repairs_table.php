<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'repairs',

            function (Blueprint $table) {

                $table->id();

                /* ===================================== */
                /* RELATIONS */
                /* ===================================== */

                $table->foreignId(
                    'damage_report_id'
                )
                ->constrained()
                ->cascadeOnDelete();

                $table->foreignId(
                    'inventory_item_id'
                )
                ->constrained()
                ->cascadeOnDelete();

                $table->foreignId(
                    'created_by'
                )
                ->constrained('users')
                ->cascadeOnDelete();

                /* ===================================== */
                /* REPAIR INFO */
                /* ===================================== */

                $table->string(
                    'vendor_name'
                )->nullable();

                $table->string(
                    'technician_name'
                )->nullable();

                $table->text(
                    'repair_details'
                )->nullable();

                /* ===================================== */
                /* COST */
                /* ===================================== */

                $table->decimal(
                    'repair_cost',
                    12,
                    2
                )->default(0);

                /* ===================================== */
                /* STATUS */
                /* ===================================== */

                $table->enum('status', [

                    'pending',

                    'in_progress',

                    'completed',

                    'cancelled',

                ])->default('pending');

                /* ===================================== */
                /* DATES */
                /* ===================================== */

                $table->timestamp(
                    'started_at'
                )->nullable();

                $table->timestamp(
                    'completed_at'
                )->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'repairs'
        );
    }
};