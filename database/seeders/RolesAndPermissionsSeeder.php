<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Cache clear করা জরুরি
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Roles তৈরি (guard_name = web)
        $roles = [
            'super_admin',
            'admin',
            'staff',
            'agent',
            'worker',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $this->command->info('✅ Roles তৈরি হয়েছে: ' . implode(', ', $roles));
    }
}