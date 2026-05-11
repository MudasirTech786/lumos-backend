<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::create([

            'name' => 'Super Admin',

            'email' => 'admin@admin.com',

            'password' => Hash::make('password@123'),
        ]);

        $superAdmin->assignRole('admin');
    }
}