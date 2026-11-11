<?php

use Spatie\Permission\Models\Permission;

it('rejects unauthenticated requests with a json 401', function () {
    $this->getJson('/api/v1/me')
        ->assertUnauthorized()
        ->assertExactJson(['error' => ['code' => 'unauthenticated', 'message' => 'Unauthenticated.']]);

    $this->getJson('/api/v1/me/menus')->assertUnauthorized();
});

it('returns json 401 even without an Accept header', function () {
    $this->get('/api/v1/me')
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'unauthenticated');
});

it('returns the user with roles and permissions', function () {
    $user = actingAsRole('editor', ['posts.view', 'posts.edit']);

    $this->getJson('/api/v1/me')
        ->assertOk()
        ->assertExactJson(['data' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_superadmin' => false,
            'roles' => ['editor'],
            'permissions' => ['posts.edit', 'posts.view'],
        ]]);
});

it('gives the superadmin every permission name', function () {
    Permission::findOrCreate('posts.view', 'api');
    Permission::findOrCreate('users.view', 'api');
    actingAsRole(config('access.superadmin_role'));

    $this->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.is_superadmin', true)
        ->assertJsonPath('data.permissions', ['posts.view', 'users.view']);
});
