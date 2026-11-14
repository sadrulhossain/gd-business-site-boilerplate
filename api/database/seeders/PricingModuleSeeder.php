<?php

namespace Database\Seeders;

class PricingModuleSeeder extends ModuleSeeder
{
    protected function permissions(): array
    {
        return ['pricing.view', 'pricing.create', 'pricing.update', 'pricing.delete'];
    }

    protected function menus(): array
    {
        return [
            ['title' => 'Content', 'sort' => 20, 'children' => [
                ['title' => 'Pricing', 'permission' => 'pricing.view', 'path' => '/pricing', 'icon' => 'price', 'sort' => 25],
            ]],
        ];
    }

    protected function grants(): array
    {
        return ['admin' => $this->permissions()];
    }
}
