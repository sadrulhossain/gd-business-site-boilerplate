<?php

namespace Database\Seeders;

use App\Models\Section;

/** The About us section (title, text, image): a screen of its own over the `about` section row. */
class AboutModuleSeeder extends ModuleSeeder
{
    protected function permissions(): array
    {
        return ['about.view', 'about.update'];
    }

    protected function menus(): array
    {
        return [
            ['title' => 'Content', 'sort' => 20, 'children' => [
                ['title' => 'About us', 'permission' => 'about.view', 'path' => '/about', 'icon' => 'info', 'sort' => 6],
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

        Section::panel('about'); // creates the row once
    }
}
