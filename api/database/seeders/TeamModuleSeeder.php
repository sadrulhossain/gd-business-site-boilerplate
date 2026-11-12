<?php

namespace Database\Seeders;

class TeamModuleSeeder extends ModuleSeeder
{
    protected function permissions(): array
    {
        return ['team.view', 'team.create', 'team.update', 'team.delete'];
    }

    protected function menus(): array
    {
        return [
            ['title' => 'Content', 'sort' => 20, 'children' => [
                ['title' => 'Team', 'permission' => 'team.view', 'path' => '/team', 'icon' => 'users', 'sort' => 40],
            ]],
        ];
    }

    protected function grants(): array
    {
        return ['admin' => $this->permissions()];
    }
}
