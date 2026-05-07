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
        Schema::table('workspace_apps', function (Blueprint $table) {
            $table->string('workspace_tab')->default('tab1')->after('url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workspace_apps', function (Blueprint $table) {
            $table->dropColumn('workspace_tab');
        });
    }
};
