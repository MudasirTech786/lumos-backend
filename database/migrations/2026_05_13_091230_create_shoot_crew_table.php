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
        Schema::create('shoot_crew', function (Blueprint $table) {

            $table->id();

            $table->foreignId('shoot_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('crew_member_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('position')->nullable();

            $table->time('call_time')->nullable();

            $table->time('wrap_time')->nullable();

            $table->decimal('rate', 10, 2)
                ->nullable();

            $table->string('status')
                ->default('assigned');

            $table->text('notes')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shoot_crew');
    }
};