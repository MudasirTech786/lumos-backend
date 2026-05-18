<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shoot_crew', function (Blueprint $table) {

            $table->dateTime('call_time')
                ->nullable()
                ->change();

            $table->dateTime('wrap_time')
                ->nullable()
                ->change();

            $table->enum('status', [
                'assigned',
                'confirmed',
                'in_progress',
                'completed',
                'cancelled'
            ])
            ->default('assigned')
            ->change();

            $table->unique([
                'shoot_id',
                'crew_member_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('shoot_crew', function (Blueprint $table) {

            $table->time('call_time')
                ->nullable()
                ->change();

            $table->time('wrap_time')
                ->nullable()
                ->change();

            $table->string('status')
                ->default('assigned')
                ->change();

            $table->dropUnique([
                'shoot_id',
                'crew_member_id'
            ]);
        });
    }
};