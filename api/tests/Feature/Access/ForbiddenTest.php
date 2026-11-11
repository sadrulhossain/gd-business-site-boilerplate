<?php

use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['api', 'auth:api', 'can:secret.view'])
        ->get('/api/v1/_test/secret', fn () => response()->json(['data' => 'ok']));
});

it('returns 403 json when the permission is missing', function () {
    actingAsRole('editor', ['posts.view']);

    $this->getJson('/api/v1/_test/secret')
        ->assertForbidden()
        ->assertJsonPath('error.code', 'forbidden');
});

it('allows a role that holds the permission', function () {
    actingAsRole('auditor', ['secret.view']);

    $this->getJson('/api/v1/_test/secret')->assertOk();
});

it('lets the superadmin through without holding the permission', function () {
    actingAsRole(config('access.superadmin_role'));

    $this->getJson('/api/v1/_test/secret')->assertOk();
});

it('returns 401 before 403 for anonymous requests', function () {
    $this->getJson('/api/v1/_test/secret')->assertUnauthorized();
});
