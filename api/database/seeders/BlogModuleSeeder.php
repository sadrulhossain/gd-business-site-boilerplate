<?php

namespace Database\Seeders;

class BlogModuleSeeder extends ModuleSeeder
{
    protected function permissions(): array
    {
        return [
            'posts.view', 'posts.create', 'posts.update', 'posts.delete',
            'categories.view', 'categories.create', 'categories.update', 'categories.delete',
        ];
    }

    protected function menus(): array
    {
        return [
            ['title' => 'Blog', 'sort' => 25, 'children' => [
                ['title' => 'Posts', 'permission' => 'posts.view', 'path' => '/posts', 'icon' => 'file-text', 'sort' => 10],
                ['title' => 'Categories', 'permission' => 'categories.view', 'path' => '/categories', 'icon' => 'tag', 'sort' => 20],
            ]],
        ];
    }

    protected function grants(): array
    {
        return ['admin' => $this->permissions()];
    }
}
