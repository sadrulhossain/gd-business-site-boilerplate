<?php

namespace Database\Seeders;

class FaqsModuleSeeder extends ModuleSeeder
{
    protected function permissions(): array
    {
        return ['faqs.view', 'faqs.create', 'faqs.update', 'faqs.delete'];
    }

    protected function menus(): array
    {
        return [
            ['title' => 'Content', 'sort' => 20, 'children' => [
                ['title' => 'FAQs', 'permission' => 'faqs.view', 'path' => '/faqs', 'icon' => 'help-circle', 'sort' => 60],
            ]],
        ];
    }

    protected function grants(): array
    {
        return ['admin' => $this->permissions()];
    }
}
