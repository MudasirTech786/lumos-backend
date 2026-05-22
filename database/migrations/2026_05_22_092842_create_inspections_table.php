<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'inspections',

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
                    'inspected_by'
                )
                ->constrained('users')
                ->cascadeOnDelete();

                /* ===================================== */
                /* CONDITION */
                /* ===================================== */

                $table->enum('condition', [

                    'excellent',

                    'good',

                    'fair',

                    'poor',

                    'critical',

                ])->default('good');

                /* ===================================== */
                /* STATUS */
                /* ===================================== */

                $table->enum('status', [

                    'passed',

                    'attention',

                    'failed',

                ])->default('passed');

                /* ===================================== */
                /* DETAILS */
                /* ===================================== */

                $table->text(
                    'notes'
                )->nullable();

                $table->text(
                    'recommendations'
                )->nullable();

                /* ===================================== */
                /* SCHEDULE */
                /* ===================================== */

                $table->timestamp(
                    'inspection_date'
                )->nullable();

                $table->timestamp(
                    'next_inspection_due'
                )->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'inspections'
        );
    }
};