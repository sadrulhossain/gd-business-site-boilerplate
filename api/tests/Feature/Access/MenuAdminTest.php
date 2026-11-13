<?php

use App\Models\Menu;
use App\Models\Section;
use Database\Seeders\DashboardModuleSeeder;
use Database\Seeders\SectionsModuleSeeder;

beforeEach(function () {
    $this->seed([DashboardModuleSeeder::class, SectionsModuleSeeder::class]);
});

it('requires authentication and permissions', function () {
    $this->getJson('/api/v1/menus')->assertUnauthorized();
    $this->putJson('/api/v1/menus/reorder', ['ids' => [1]])->assertUnauthorized();

    actingAsRole('nobody');
    $this->getJson('/api/v1/menus')->assertForbidden();
    $this->putJson('/api/v1/menus/reorder', ['ids' => [1]])->assertForbidden();
});

it('lists the whole tree depth-first with depth', function () {
    actingAsRole('viewer', ['menus.view']);

    $rows = $this->getJson('/api/v1/menus')->assertOk()->json('data');
    $group = collect($rows)->firstWhere('title', 'Content');
    $child = collect($rows)->firstWhere('title', 'Sections');

    expect($group['depth'])->toBe(0)->and($child['depth'])->toBe(1)->and($child['parent_id'])->toBe($group['id']);
});

it('edits title and icon only', function () {
    actingAsRole('editor', ['menus.update']);
    $menu = Menu::where('path', '/sections')->firstOrFail();

    $this->patchJson("/api/v1/menus/{$menu->id}", ['title' => 'Blocks', 'icon' => 'box', 'path' => '/hacked'])
        ->assertOk()->assertJsonPath('data.title', 'Blocks');

    expect($menu->refresh()->path)->toBe('/sections');
    $this->patchJson("/api/v1/menus/{$menu->id}", ['title' => ''])->assertUnprocessable()->assertJsonStructure(['error' => ['details' => ['title']]]);
});

it('reorders siblings and rejects mixed parents', function () {
    actingAsRole('editor', ['menus.update']);
    $top = Menu::whereNull('parent_id')->orderBy('sort')->pluck('id')->all();
    $child = Menu::where('path', '/sections')->value('id');

    $this->putJson('/api/v1/menus/reorder', ['ids' => array_reverse($top)])->assertOk();
    expect(Menu::whereNull('parent_id')->orderBy('sort')->pluck('id')->all())->toBe(array_reverse($top));

    $this->putJson('/api/v1/menus/reorder', ['ids' => [$top[0], $child]])->assertUnprocessable()->assertJsonStructure(['error' => ['details' => ['ids']]]);
    $this->putJson('/api/v1/menus/reorder', ['ids' => [999999]])->assertUnprocessable()->assertJsonStructure(['error' => ['details' => ['ids.0']]]);
});

it('reorders sections', function () {
    actingAsRole('editor', ['sections.update']);
    $ids = Section::orderBy('sort_order')->pluck('id')->reverse()->values()->all();

    $this->putJson('/api/v1/sections/reorder', ['ids' => $ids])->assertOk();

    expect(Section::orderBy('sort_order')->pluck('id')->all())->toBe($ids);
});

it('forbids reordering sections without sections.update', function () {
    actingAsRole('viewer', ['sections.view']);

    $this->putJson('/api/v1/sections/reorder', ['ids' => [1]])->assertForbidden();
});
