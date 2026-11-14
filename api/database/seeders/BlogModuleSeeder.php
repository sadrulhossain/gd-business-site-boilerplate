<?php

namespace Database\Seeders;

use App\Models\Menu;

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
            ['title' => 'Content', 'sort' => 20, 'children' => [
                ['title' => 'Posts', 'permission' => 'posts.view', 'path' => '/posts', 'icon' => 'file-text', 'sort' => 70],
                ['title' => 'Categories', 'permission' => 'categories.view', 'path' => '/categories', 'icon' => 'tag', 'sort' => 80],
            ]],
        ];
    }

    protected function grants(): array
    {
        return ['admin' => $this->permissions()];
    }

    public function run(): void
    {
        parent::run();

        // Posts and categories used to sit under their own "Blog" group; they live under Content now.
        Menu::query()->where('title', 'Blog')->whereNull('permission_name')->whereDoesntHave('children')->delete();
    }
}
