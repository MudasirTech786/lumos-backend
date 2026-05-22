<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'damage_reports',

            function (Blueprint $table) {

                $table->id();

                /* ===================================== */
                /* RELATIONS */
                /* ===================================== */

                $table->foreignId(
                    'inventory_item_id'
                )->constrained()
                ->cascadeOnDelete();

                $table->foreignId(
                    'inventory_usage_id'
                )
                ->nullable()
                ->constrained()
                ->nullOnDelete();

                $table->foreignId(
                    'reported_by'
                )
                ->constrained('users')
                ->cascadeOnDelete();

                /* ===================================== */
                /* DAMAGE INFO */
                /* ===================================== */

                $table->enum('severity', [

                    'low',

                    'medium',

                    'high',

                    'critical',

                ])->default('low');

                $table->string(
                    'issue_type'
                );

                $table->text(
                    'description'
                )->nullable();

                /* ===================================== */
                /* WORKFLOW */
                /* ===================================== */

                $table->enum('status', [

                    'pending',

                    'inspection',

                    'repair',

                    'resolved',

                    'writeoff',

                ])->default('pending');

                /* ===================================== */
                /* COST */
                /* ===================================== */

                $table->decimal(
                    'estimated_cost',
                    12,
                    2
                )->nullable();

                /* ===================================== */
                /* META */
                /* ===================================== */

                $table->timestamp(
                    'reported_at'
                )->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'damage_reports'
        );
    }
};