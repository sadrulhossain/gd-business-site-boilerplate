<?php

namespace Database\Seeders;

class ServicesModuleSeeder extends ModuleSeeder
{
    protected function permissions(): array
    {
        return ['services.view', 'services.create', 'services.update', 'services.delete'];
    }

    protected function menus(): array
    {
        return [
            ['title' => 'Content', 'sort' => 20, 'children' => [
                ['title' => 'Services', 'permission' => 'services.view', 'path' => '/services', 'icon' => 'briefcase', 'sort' => 20],
            ]],
        ];
    }

    protected function grants(): array
    {
        return ['admin' => $this->permissions()];
    }
}
