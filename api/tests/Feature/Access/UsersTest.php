<?php

use App\Models\Menu;
use App\Models\User;
use Database\Seeders\AccessModuleSeeder;
use Spatie\Permission\Models\Role;

it('requires authentication and users.view', function () {
    $this->getJson('/api/v1/users')->assertUnauthorized();

    actingAsRole('nobody');
    $this->getJson('/api/v1/users')->assertForbidden();
});

it('lists users paginated with their roles', function () {
    $me = actingAsRole('viewer', ['users.view']);

    $this->getJson('/api/v1/users')
        ->assertOk()
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('data.0.id', $me->id)
        ->assertJsonPath('data.0.roles.0', 'viewer');
});

it('creates a user with roles and hashes the password', function () {
    actingAsRole('manager', ['users.create']);
    Role::findOrCreate('editor', 'api');

    $this->postJson('/api/v1/users', ['name' => 'Ann', 'email' => 'ann@example.com', 'password' => 'a-long-password-1', 'roles' => ['editor']])
        ->assertCreated()
        ->assertJsonPath('data.roles.0', 'editor');

    expect(User::where('email', 'ann@example.com')->first()->password)->not->toBe('a-long-password-1');
});

it('rejects bad input with field keys and refuses the superadmin role', function () {
    actingAsRole('manager', ['users.create']);
    Role::findOrCreate(config('access.superadmin_role'), 'api');

    $this->postJson('/api/v1/users', ['name' => '', 'email' => 'nope', 'password' => 'short', 'roles' => ['superadmin']])
        ->assertUnprocessable()
        ->assertJsonStructure(['error' => ['details' => ['name', 'email', 'password', 'roles.0']]]);
});

it('keeps the password when it is blank on update', function () {
    actingAsRole('manager', ['users.update']);
    $target = User::factory()->create();
    $hash = $target->password;

    $this->patchJson("/api/v1/users/{$target->id}", ['name' => 'Renamed', 'password' => null])->assertOk();

    expect($target->refresh()->name)->toBe('Renamed')->and($target->password)->toBe($hash);
});

it('keeps the superadmin role when a superadmin edits roles', function () {
    $actor = actingAsRole('manager', ['users.update']);
    $actor->assignRole(Role::findOrCreate(config('access.superadmin_role'), 'api'));
    $target = User::factory()->create()->assignRole(config('access.superadmin_role'));

    $this->patchJson("/api/v1/users/{$target->id}", ['roles' => []])->assertOk();

    expect($target->refresh()->isSuperadmin())->toBeTrue();
});

it('forbids a non-superadmin from changing or deleting a superadmin', function () {
    actingAsRole('manager', ['users.update', 'users.delete']);
    $root = User::factory()->create()->assignRole(Role::findOrCreate(config('access.superadmin_role'), 'api'));

    $this->patchJson("/api/v1/users/{$root->id}", ['name' => 'x'])->assertForbidden();
    $this->deleteJson("/api/v1/users/{$root->id}")->assertForbidden();
});

it('deletes a user but never yourself', function () {
    $me = actingAsRole('manager', ['users.delete']);
    $other = User::factory()->create();

    $this->deleteJson("/api/v1/users/{$me->id}")->assertForbidden();
    $this->deleteJson("/api/v1/users/{$other->id}")->assertNoContent();
    expect(User::find($other->id))->toBeNull();
});

it('seeds access permissions and menus idempotently', function () {
    $this->seed(AccessModuleSeeder::class);
    $this->seed(AccessModuleSeeder::class);

    expect(Menu::whereIn('path', ['/users', '/roles', '/menus'])->count())->toBe(3);
});
