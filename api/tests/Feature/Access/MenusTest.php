<?php

use App\Models\Menu;

beforeEach(function () {
    Menu::create(['permission_name' => 'dashboard.view', 'title' => 'Dashboard', 'sort' => 0, 'path' => '/']);

    $content = Menu::create(['title' => 'Content', 'sort' => 10]);
    Menu::create(['parent_id' => $content->id, 'permission_name' => 'posts.view', 'title' => 'Posts', 'sort' => 1]);
    Menu::create(['parent_id' => $content->id, 'permission_name' => 'pages.view', 'title' => 'Pages', 'sort' => 2]);

    $settings = Menu::create(['title' => 'Settings', 'sort' => 20]);
    Menu::create(['parent_id' => $settings->id, 'permission_name' => 'settings.view', 'title' => 'General', 'sort' => 1]);
});

/** @return list<string> */
function menuTitles(array $items): array
{
    return collect($items)->flatMap(fn ($i) => [$i['title'], ...menuTitles($i['children'])])->all();
}

it('shows a restricted role only its menus', function () {
    actingAsRole('editor', ['dashboard.view', 'posts.view']);

    $response = $this->getJson('/api/v1/me/menus')->assertOk();

    expect(menuTitles($response->json('data')))->toBe(['Dashboard', 'Content', 'Posts']);
});

it('hides groups that have no visible children', function () {
    actingAsRole('viewer', ['dashboard.view']);

    $response = $this->getJson('/api/v1/me/menus')->assertOk();

    expect(menuTitles($response->json('data')))->toBe(['Dashboard']);
});

it('returns an empty list for a role without menu permissions', function () {
    actingAsRole('nobody');

    $this->getJson('/api/v1/me/menus')->assertOk()->assertExactJson(['data' => []]);
});

it('shows the superadmin every menu', function () {
    actingAsRole(config('access.superadmin_role'));

    $response = $this->getJson('/api/v1/me/menus')->assertOk();

    expect(menuTitles($response->json('data')))
        ->toBe(['Dashboard', 'Content', 'Posts', 'Pages', 'Settings', 'General']);
});
