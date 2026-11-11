<?php

use App\Models\Setting;
use Database\Seeders\SettingsModuleSeeder;
use Illuminate\Database\QueryException;

beforeEach(fn () => $this->seed(SettingsModuleSeeder::class));

it('requires authentication', function () {
    $this->getJson('/api/v1/settings')->assertUnauthorized();
});

it('forbids reading without settings.view', function () {
    actingAsRole('nobody');

    $this->getJson('/api/v1/settings')->assertForbidden()->assertJsonPath('error.code', 'forbidden');
});

it('shows the seeded defaults', function () {
    actingAsRole('viewer', ['settings.view']);

    $this->getJson('/api/v1/settings')
        ->assertOk()
        ->assertJsonPath('data.site_name', 'My Business')
        ->assertJsonPath('data.site_name_2nd', null)
        ->assertJsonPath('data.hours.mon.open', '09:00')
        ->assertJsonPath('data.hours.sun.closed', true)
        ->assertJsonPath('data.social_links', []);
});

it('forbids updating with view-only access, before validating', function () {
    actingAsRole('viewer', ['settings.view']);

    $this->putJson('/api/v1/settings', ['email' => 'not-an-email'])->assertForbidden();
});

it('updates both languages, hours and social links', function () {
    actingAsRole('editor', ['settings.view', 'settings.update']);

    $this->patchJson('/api/v1/settings', [
        'site_name' => 'Acme',
        'site_name_2nd' => 'এক্মি',
        'email' => 'hello@acme.test',
        'hours' => ['mon' => ['open' => '08:30', 'close' => '18:00', 'closed' => false]],
        'social_links' => ['facebook' => 'https://facebook.com/acme'],
    ])
        ->assertOk()
        ->assertJsonPath('data.site_name', 'Acme')
        ->assertJsonPath('data.site_name_2nd', 'এক্মি')
        ->assertJsonPath('data.social_links.facebook', 'https://facebook.com/acme');

    $setting = Setting::current();
    expect($setting->tagline)->toBe('Welcome') // untouched by a partial update
        ->and($setting->hours)->toBe(['mon' => ['open' => '08:30', 'close' => '18:00', 'closed' => false]]);
});

it('rejects invalid input', function () {
    actingAsRole('editor', ['settings.view', 'settings.update']);

    $response = $this->putJson('/api/v1/settings', [
        'site_name' => '',
        'email' => 'nope',
        'hours' => ['funday' => ['closed' => true]],
        'social_links' => ['facebook' => 'not a url'],
    ])->assertStatus(422)->assertJsonPath('error.code', 'validation_failed');

    expect(array_keys($response->json('error.details')))
        ->toEqualCanonicalizing(['site_name', 'email', 'hours', 'social_links.facebook']);
});

it('is edit-only and a singleton', function () {
    actingAsRole('editor', ['settings.view', 'settings.update']);

    $this->postJson('/api/v1/settings', [])->assertStatus(405);
    $this->deleteJson('/api/v1/settings')->assertStatus(405);

    expect(fn () => Setting::unguarded(fn () => Setting::query()->create(['id' => 2, 'site_name' => 'x'])))
        ->toThrow(QueryException::class);
});
