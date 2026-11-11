<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([config('access.superadmin_role'), 'admin'] as $name) {
            Role::findOrCreate($name, ModuleSeeder::GUARD);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
