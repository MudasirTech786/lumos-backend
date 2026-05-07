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
        Schema::table('crew_members', function (Blueprint $table) {

            $table->string('profile_photo')->nullable();

            $table->enum('employment_type', [
                'freelancer',
                'full_time',
                'part_time'
            ])->default('freelancer');

            $table->decimal('hourly_rate', 10, 2)->default(0);

            $table->json('skills')->nullable();

            $table->date('joining_date')->nullable();

            $table->text('address')->nullable();

            $table->string('cnic')->nullable();

            $table->string('emergency_contact')->nullable();

            $table->boolean('is_active')->default(true);

            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crew_members', function (Blueprint $table) {
            //
        });
    }
};
