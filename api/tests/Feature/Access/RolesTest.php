<?php

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('requires authentication and roles.view', function () {
    $this->getJson('/api/v1/roles')->assertUnauthorized();
    $this->getJson('/api/v1/permissions')->assertUnauthorized();

    actingAsRole('nobody');
    $this->getJson('/api/v1/roles')->assertForbidden();
    $this->getJson('/api/v1/permissions')->assertForbidden();
});

it('lists roles without the superadmin and lists permission names', function () {
    actingAsRole('viewer', ['roles.view']);
    Role::findOrCreate(config('access.superadmin_role'), 'api');

    $names = collect($this->getJson('/api/v1/roles')->assertOk()->json('data'))->pluck('name');
    expect($names)->toContain('viewer')->not->toContain('superadmin');

    $this->getJson('/api/v1/permissions')->assertOk()->assertJsonPath('data.0', 'roles.view');
});

it('creates a role with permissions', function () {
    actingAsRole('manager', ['roles.create', 'faqs.view', 'faqs.update']);

    $this->postJson('/api/v1/roles', ['name' => 'editor', 'permissions' => ['faqs.view']])
        ->assertCreated()
        ->assertJsonPath('data.permissions.0', 'faqs.view');
});

it('rejects a duplicate, the superadmin name and a bad name', function () {
    actingAsRole('manager', ['roles.create']);

    $this->postJson('/api/v1/roles', ['name' => 'manager'])->assertUnprocessable()->assertJsonStructure(['error' => ['details' => ['name']]]);
    $this->postJson('/api/v1/roles', ['name' => 'superadmin'])->assertUnprocessable()->assertJsonStructure(['error' => ['details' => ['name']]]);
    $this->postJson('/api/v1/roles', ['name' => 'Bad Name'])->assertUnprocessable()->assertJsonStructure(['error' => ['details' => ['name']]]);
});

it('only lets a non-superadmin grant permissions they hold', function () {
    actingAsRole('manager', ['roles.create', 'roles.update', 'faqs.view']);
    Permission::findOrCreate('users.delete', 'api');
    $role = Role::findOrCreate('editor', 'api');

    $this->postJson('/api/v1/roles', ['name' => 'x', 'permissions' => ['users.delete']])
        ->assertUnprocessable()->assertJsonStructure(['error' => ['details' => ['permissions.0']]]);
    $this->patchJson("/api/v1/roles/{$role->id}", ['permissions' => ['faqs.view']])->assertOk();
});

it('syncs permissions on update', function () {
    actingAsRole('manager', ['roles.update', 'faqs.view', 'faqs.update']);
    $role = Role::findOrCreate('editor', 'api')->givePermissionTo(Permission::findOrCreate('faqs.view', 'api'));

    $this->patchJson("/api/v1/roles/{$role->id}", ['permissions' => ['faqs.update']])->assertOk();

    expect($role->refresh()->permissions->pluck('name')->all())->toBe(['faqs.update']);
});

it('never changes or deletes the superadmin role', function () {
    actingAsRole('manager', ['roles.update', 'roles.delete']);
    $root = Role::findOrCreate(config('access.superadmin_role'), 'api');

    $this->patchJson("/api/v1/roles/{$root->id}", ['name' => 'x'])->assertForbidden();
    $this->deleteJson("/api/v1/roles/{$root->id}")->assertForbidden();
});

it('deletes a role', function () {
    actingAsRole('manager', ['roles.delete']);
    $role = Role::findOrCreate('temp', 'api');

    $this->deleteJson("/api/v1/roles/{$role->id}")->assertNoContent();
    expect(Role::where('name', 'temp')->exists())->toBeFalse();
});
