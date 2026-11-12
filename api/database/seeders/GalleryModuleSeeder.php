<?php

namespace Database\Seeders;

class GalleryModuleSeeder extends ModuleSeeder
{
    protected function permissions(): array
    {
        return ['gallery.view', 'gallery.create', 'gallery.update', 'gallery.delete'];
    }

    protected function menus(): array
    {
        return [
            ['title' => 'Content', 'sort' => 20, 'children' => [
                ['title' => 'Gallery', 'permission' => 'gallery.view', 'path' => '/gallery', 'icon' => 'image', 'sort' => 50],
            ]],
        ];
    }

    protected function grants(): array
    {
        return ['admin' => $this->permissions()];
    }
}
