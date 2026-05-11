<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Run seeders in sequence
        $this->call(PermissionSeeder::class);
        $this->call(UserSeeder::class);
    }
}