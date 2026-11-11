<?php

use App\Models\Menu;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;

const TEST_CLIENT_ID = '0194d2c0-0000-7000-8000-000000000001';

beforeEach(function () {
    config([
        'access.superadmin.email' => 'root@example.test',
        'access.superadmin.password' => 'a-long-test-password',
        'access.oauth_admin_client.id' => TEST_CLIENT_ID,
        'access.oauth_admin_client.redirect_uri' => 'http://admin.example.test/auth/callback',
    ]);
});

it('seeds roles, the dashboard module, the superadmin and the admin client', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Role::pluck('name')->sort()->values()->all())->toBe(['admin', 'superadmin']);
    expect(Menu::where('permission_name', 'dashboard.view')->exists())->toBeTrue();
    expect(Role::findByName('admin', 'api')->hasPermissionTo('dashboard.view'))->toBeTrue();

    $root = User::where('email', 'root@example.test')->firstOrFail();
    expect($root->isSuperadmin())->toBeTrue();
    expect(Hash::check('a-long-test-password', $root->password))->toBeTrue();
    expect(Gate::forUser($root)->allows('anything.at.all'))->toBeTrue();

    $client = Passport::client()->newQuery()->findOrFail(TEST_CLIENT_ID);
    expect($client->confidential())->toBeFalse()
        ->and($client->redirect_uris)->toBe(['http://admin.example.test/auth/callback'])
        ->and($client->grant_types)->toBe(['authorization_code', 'refresh_token'])
        ->and($client->skipsAuthorization($root, []))->toBeTrue();
});

it('is idempotent', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe(1)
        ->and(Menu::count())->toBe(1)
        ->and(Passport::client()->newQuery()->count())->toBe(1);
});

it('skips the superadmin and client when env is not set', function () {
    config(['access.superadmin.password' => null, 'access.oauth_admin_client.id' => null]);

    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe(0)->and(Passport::client()->newQuery()->count())->toBe(0);
});
