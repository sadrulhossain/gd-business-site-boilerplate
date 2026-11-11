<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature');

/**
 * Create a user holding a role with the given permissions and authenticate as it on the api guard.
 *
 * @param  list<string>  $permissions
 */
function actingAsRole(string $role, array $permissions = []): User
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $role = Role::findOrCreate($role, 'api');

    foreach ($permissions as $name) {
        $role->givePermissionTo(Permission::findOrCreate($name, 'api'));
    }

    $user = User::factory()->create()->assignRole($role);

    Passport::actingAs($user, [], 'api');

    return $user;
}
