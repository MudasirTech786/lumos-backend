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
        Schema::table('shoot_logistics', function (Blueprint $table) {

            $table->enum('logistics_type', [

                'internal_transport',
                'uber',
                'equipment_transport',
                'client_transport',
                'talent_transport',

            ])->default('internal_transport');

            $table->string('vendor_name')
                ->nullable();

            $table->string('reference_number')
                ->nullable();

            $table->decimal('estimated_cost', 10, 2)
                ->nullable();

                
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shoot_logistics', function (Blueprint $table) {
            //
        });
    }
};
