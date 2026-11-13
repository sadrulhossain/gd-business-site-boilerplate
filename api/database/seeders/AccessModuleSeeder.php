<?php

namespace Database\Seeders;

/** Users, roles and the menu tree. Not granted to `admin`: only the superadmin manages access until a role is given these. */
class AccessModuleSeeder extends ModuleSeeder
{
    protected function permissions(): array
    {
        return [
            'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.view', 'roles.create', 'roles.update', 'roles.delete',
            'menus.view', 'menus.update',
        ];
    }

    protected function menus(): array
    {
        return [
            ['title' => 'Access', 'sort' => 80, 'children' => [
                ['title' => 'Users', 'permission' => 'users.view', 'path' => '/users', 'icon' => 'users', 'sort' => 10],
                ['title' => 'Roles', 'permission' => 'roles.view', 'path' => '/roles', 'icon' => 'shield', 'sort' => 20],
                ['title' => 'Menus', 'permission' => 'menus.view', 'path' => '/menus', 'icon' => 'list', 'sort' => 30],
            ]],
        ];
    }
}
