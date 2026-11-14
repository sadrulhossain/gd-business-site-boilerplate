<?php

use App\Models\Section;
use Database\Seeders\SectionsModuleSeeder;

beforeEach(fn () => $this->seed(SectionsModuleSeeder::class));

it('requires authentication and sections.view', function () {
    $this->getJson('/api/v1/sections')->assertUnauthorized();

    actingAsRole('nobody');
    $this->getJson('/api/v1/sections')->assertForbidden();
});

it('lists sections in sort order', function () {
    actingAsRole('viewer', ['sections.view']);

    $this->getJson('/api/v1/sections')
        ->assertOk()
        ->assertJsonCount(9, 'data')
        ->assertJsonPath('data.0.key', 'hero')
        ->assertJsonPath('data.0.image', null);
});

it('shows one section', function () {
    actingAsRole('viewer', ['sections.view']);

    $this->getJson('/api/v1/sections/'.Section::where('key', 'about')->value('id'))
        ->assertOk()
        ->assertJsonPath('data.key', 'about')
        ->assertJsonPath('data.is_visible', true);
});

it('updates both languages and ignores the immutable key', function () {
    actingAsRole('editor', ['sections.view', 'sections.update']);
    $section = Section::where('key', 'about')->firstOrFail();

    $this->patchJson("/api/v1/sections/{$section->id}", [
        'key' => 'hacked',
        'title' => 'About',
        'title_2nd' => 'সম্পর্কে',
        'content' => 'Hello',
        'is_visible' => false,
        'sort_order' => 5,
    ])
        ->assertOk()
        ->assertJsonPath('data.key', 'about')
        ->assertJsonPath('data.title_2nd', 'সম্পর্কে')
        ->assertJsonPath('data.is_visible', false);

    expect($section->refresh()->sort_order)->toBe(5);
});

it('forbids updating without sections.update', function () {
    actingAsRole('viewer', ['sections.view']);
    $id = Section::where('key', 'about')->value('id');

    $this->patchJson("/api/v1/sections/{$id}", ['title' => 'x'])->assertForbidden();
    expect(Section::find($id)->title)->toBe('About us');
});

it('validates input', function () {
    actingAsRole('editor', ['sections.view', 'sections.update']);
    $id = Section::where('key', 'about')->value('id');

    $response = $this->patchJson("/api/v1/sections/{$id}", ['title' => '', 'sort_order' => -1, 'is_visible' => 'maybe'])
        ->assertStatus(422);

    expect(array_keys($response->json('error.details')))->toEqualCanonicalizing(['title', 'sort_order', 'is_visible']);
});

it('has no create or delete', function () {
    actingAsRole('editor', ['sections.view', 'sections.update']);
    $id = Section::value('id');

    $this->postJson('/api/v1/sections', [])->assertStatus(405);
    $this->deleteJson("/api/v1/sections/{$id}")->assertStatus(405);
});
