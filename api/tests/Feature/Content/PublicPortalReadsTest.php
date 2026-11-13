<?php

use App\Models\Section;
use Database\Seeders\SectionsModuleSeeder;

it('serves settings publicly without authentication', function () {
    $this->getJson('/api/v1/public/settings')
        ->assertOk()
        ->assertJsonPath('data.site_name', 'My Business')
        ->assertJsonStructure(['data' => ['site_name', 'tagline', 'address', 'phone', 'email', 'hours', 'social_links']]);
});

it('serves only visible sections in order, and collapses languages with ?lang', function () {
    $this->seed(SectionsModuleSeeder::class);
    Section::where('key', 'about')->update(['is_visible' => false]);
    Section::where('key', 'hero')->update(['title_2nd' => 'স্বাগতম']);

    $keys = collect($this->getJson('/api/v1/public/sections')->assertOk()->json('data'))->pluck('key');
    expect($keys->all())->toBe(['hero', 'services', 'contact'])->and($keys)->not->toContain('about');

    config(['locales.supported' => ['en', 'bn'], 'locales.second' => 'bn']);
    $this->getJson('/api/v1/public/sections?lang=bn')->assertJsonPath('data.0.title', 'স্বাগতম')->assertJsonMissingPath('data.0.title_2nd');
});
