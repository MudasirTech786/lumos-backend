<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE asset_scan_logs
            MODIFY action ENUM(
                'lookup',
                'checkout',
                'return',
                'damage',
                'repair',
                'writeoff',
                'inspection',
                'allocated',
                'returned',
                'created',
                'status_changed'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE asset_scan_logs
            MODIFY action ENUM(
                'lookup',
                'checkout',
                'return',
                'damage',
                'repair',
                'writeoff',
                'inspection'
            ) NOT NULL
        ");
    }
};