<?php

namespace Database\Seeders;

class TestimonialsModuleSeeder extends ModuleSeeder
{
    protected function permissions(): array
    {
        return ['testimonials.view', 'testimonials.create', 'testimonials.update', 'testimonials.delete'];
    }

    protected function menus(): array
    {
        return [
            ['title' => 'Content', 'sort' => 20, 'children' => [
                ['title' => 'Testimonials', 'permission' => 'testimonials.view', 'path' => '/testimonials', 'icon' => 'message-square', 'sort' => 30],
            ]],
        ];
    }

    protected function grants(): array
    {
        return ['admin' => $this->permissions()];
    }
}
