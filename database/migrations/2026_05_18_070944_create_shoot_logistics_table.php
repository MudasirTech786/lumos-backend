<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations.
     */
    public function up(): void
    {
        Schema::create(
            'shoot_logistics',
            function (Blueprint $table) {

                $table->id();

                /*
                |--------------------------------------------------------------------------
                | SHOOT
                |--------------------------------------------------------------------------
                */

                $table->foreignId('shoot_id')
                    ->constrained()
                    ->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | TRANSPORT
                |--------------------------------------------------------------------------
                */

                $table->string('transport_type')
                    ->nullable();

                $table->string('vehicle')
                    ->nullable();

                $table->string('driver_name')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | PICKUP
                |--------------------------------------------------------------------------
                */

                $table->string('pickup_location')
                    ->nullable();

                $table->dateTime('pickup_time')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | DROPOFF
                |--------------------------------------------------------------------------
                */

                $table->string('dropoff_location')
                    ->nullable();

                $table->dateTime('dropoff_time')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | STATUS
                |--------------------------------------------------------------------------
                */

                $table->enum('status', [

                    'pending',
                    'ready',
                    'in_transit',
                    'completed',
                    'delayed',
                ])->default('pending');

                /*
                |--------------------------------------------------------------------------
                | NOTES
                |--------------------------------------------------------------------------
                */

                $table->text('notes')
                    ->nullable();

                $table->timestamps();
            }
        );
    }

    /**
     * Reverse migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'shoot_logistics'
        );
    }
};
