<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            // USERS
            'users.create',
            'users.view',
            'users.edit',
            'users.delete',

            // ROLES
            'roles.create',
            'roles.view',
            'roles.edit',
            'roles.delete',

            // PERMISSIONS
            'permissions.create',
            'permissions.view',
            'permissions.edit',
            'permissions.delete',

            // HR
            'hr.create',
            'hr.view',
            'hr.edit',
            'hr.delete',

            // EMPLOYEES
            'employees.create',
            'employees.view',
            'employees.edit',
            'employees.delete',

            // CREW
            'crew.create',
            'crew.view',
            'crew.edit',
            'crew.delete',
            
            // WORKSPACES
            'workspaces.create',
            'workspaces.view',
            'workspaces.edit',
            'workspaces.delete',
            
            // LEAVES
            'leaves.create',
            'leaves.view',
            'leaves.edit',
            'leaves.delete',
            'leaves.view_own'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ADMIN ROLE
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // ✅ ASSIGN ALL PERMISSIONS TO ADMIN ROLE
        $adminRole->syncPermissions(Permission::all());
    }
}