<?php

use App\Models\Menu;
use App\Models\Section;
use App\Models\Setting;
use Database\Seeders\SectionsModuleSeeder;
use Database\Seeders\SettingsModuleSeeder;
use Spatie\Permission\Models\Role;

it('seeds core sections on and optional sections off', function () {
    $this->seed(SectionsModuleSeeder::class);

    expect(Section::where('is_visible', true)->orderBy('sort_order')->pluck('key')->all())
        ->toBe(['hero', 'about', 'services', 'contact'])
        ->and(Section::where('is_visible', false)->orderBy('sort_order')->pluck('key')->all())
        ->toBe(['testimonials', 'gallery', 'faq', 'team']);
});

it('seeds default settings, permissions, menus and admin grants', function () {
    $this->seed([SectionsModuleSeeder::class, SettingsModuleSeeder::class]);

    expect(Setting::count())->toBe(1)
        ->and(Menu::whereIn('permission_name', ['sections.view', 'settings.view'])->count())->toBe(2)
        ->and(Role::findByName('admin', 'api')->hasAllPermissions(['sections.view', 'sections.update', 'settings.view', 'settings.update']))->toBeTrue();
});

it('never overwrites admin edits when seeded again', function () {
    $this->seed([SectionsModuleSeeder::class, SettingsModuleSeeder::class]);

    Section::where('key', 'hero')->update(['title' => 'Edited', 'is_visible' => false]);
    Setting::current()->update(['site_name' => 'Acme']);

    $this->seed([SectionsModuleSeeder::class, SettingsModuleSeeder::class]);

    expect(Section::count())->toBe(8)
        ->and(Section::where('key', 'hero')->first()->title)->toBe('Edited')
        ->and(Section::where('key', 'hero')->first()->is_visible)->toBeFalse()
        ->and(Setting::current()->site_name)->toBe('Acme');
});
