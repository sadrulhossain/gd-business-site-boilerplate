<?php

namespace Database\Seeders;

use App\Models\Section;

/** The landing-page hero (title, description, banner): a screen of its own over the `hero` section row. */
class HeroModuleSeeder extends ModuleSeeder
{
    protected function permissions(): array
    {
        return ['hero.view', 'hero.update'];
    }

    protected function menus(): array
    {
        return [
            ['title' => 'Content', 'sort' => 20, 'children' => [
                ['title' => 'Hero', 'permission' => 'hero.view', 'path' => '/hero', 'icon' => 'home', 'sort' => 5],
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

        Section::hero(); // creates the row once
    }
}
