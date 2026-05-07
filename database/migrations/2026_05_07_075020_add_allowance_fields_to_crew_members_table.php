<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crew_members', function (Blueprint $table) {

            $table->decimal('commission', 10, 2)
                  ->default(0)
                  ->after('hourly_rate');

            $table->decimal('home_allowance', 10, 2)
                  ->default(0)
                  ->after('commission');

            $table->decimal('fuel_allowance', 10, 2)
                  ->default(0)
                  ->after('home_allowance');

            $table->decimal('others', 10, 2)
                  ->default(0)
                  ->after('fuel_allowance');
        });
    }

    public function down(): void
    {
        Schema::table('crew_members', function (Blueprint $table) {

            $table->dropColumn([
                'commission',
                'home_allowance',
                'fuel_allowance',
                'others',
            ]);
        });
    }
};