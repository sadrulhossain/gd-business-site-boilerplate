<?php

namespace Database\Seeders;

class ContactModuleSeeder extends ModuleSeeder
{
    protected function permissions(): array
    {
        return ['contact.view', 'contact.update'];
    }

    protected function menus(): array
    {
        return [
            ['title' => 'Messages', 'permission' => 'contact.view', 'path' => '/messages', 'icon' => 'mail', 'sort' => 30],
        ];
    }

    protected function grants(): array
    {
        return ['admin' => $this->permissions()];
    }
}
