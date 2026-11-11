<?php

namespace Database\Seeders;

/** Reference module seeder: copy this shape for every new module. */
class DashboardModuleSeeder extends ModuleSeeder
{
    protected function permissions(): array
    {
        return ['dashboard.view'];
    }

    protected function menus(): array
    {
        return [
            ['title' => 'Dashboard', 'permission' => 'dashboard.view', 'path' => '/', 'icon' => 'home', 'sort' => 0],
        ];
    }

    protected function grants(): array
    {
        return ['admin' => ['dashboard.view']];
    }
}
