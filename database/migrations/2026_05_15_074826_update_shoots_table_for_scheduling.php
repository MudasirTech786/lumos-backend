<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shoots', function (Blueprint $table) {

            $table->dateTime('start_datetime')
                ->nullable()
                ->after('location');

            $table->dateTime('end_datetime')
                ->nullable()
                ->after('start_datetime');

            $table->dropColumn('shoot_date');
        });
    }

    public function down(): void
    {
        Schema::table('shoots', function (Blueprint $table) {

            $table->date('shoot_date')
                ->nullable();

            $table->dropColumn([
                'start_datetime',
                'end_datetime'
            ]);
        });
    }
};