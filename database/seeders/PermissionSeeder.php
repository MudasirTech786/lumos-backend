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

        // ================= ADMIN =================
        $admin = Role::firstOrCreate([
            'name' => 'admin'
        ]);

        $admin->syncPermissions(Permission::all());

        // ================= HR MANAGER =================
        $hr = Role::firstOrCreate([
            'name' => 'hr_manager'
        ]);

        $hr->syncPermissions([
            'crew.view',
            'crew.create',
            'crew.edit',
            'crew.delete',
            'leaves.create',
            'leaves.view',
            'leaves.edit',
            'leaves.delete',
            'leaves.view_own',
            'employees.create',
            'employees.view',
            'employees.edit',
            'employees.delete',
        ]);

        // ================= VIEWER =================
        $viewer = Role::firstOrCreate([
            'name' => 'viewer'
        ]);

        $viewer->syncPermissions([
            'crew.view',
            'employees.view',
            'leaves.view',
        ]);
    }
}
