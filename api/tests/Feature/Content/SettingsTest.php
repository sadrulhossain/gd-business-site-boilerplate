<?php

use App\Models\Setting;
use Database\Seeders\SettingsModuleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

it('uploads, replaces and removes the logo and favicon', function () {
    Storage::fake('uploads');
    actingAsRole('editor', ['settings.view', 'settings.update']);
    $accept = ['Accept' => 'application/json'];

    foreach (['logo', 'favicon'] as $kind) {
        $this->post("/api/v1/settings/{$kind}", ['image' => UploadedFile::fake()->image('a.png', 200, 200)], $accept)
            ->assertOk()
            ->assertJsonStructure(['data' => [$kind => ['url', 'webp']]]);
        $path = Setting::current()->{$kind};
        expect($path)->toMatch('#^site/[a-z0-9]{32}/original\.png$#');

        $this->deleteJson("/api/v1/settings/{$kind}")->assertOk()->assertJsonPath("data.{$kind}", null);
    }

    expect(Storage::disk('uploads')->allFiles())->toBe([]);

    actingAsRole('viewer', ['settings.view']);
    $this->post('/api/v1/settings/logo', ['image' => UploadedFile::fake()->image('a.png')], $accept)->assertForbidden();
});

it('accepts an .ico favicon but not as a logo', function () {
    Storage::fake('uploads');
    actingAsRole('editor', ['settings.view', 'settings.update']);
    $accept = ['Accept' => 'application/json'];

    $this->post('/api/v1/settings/favicon', ['image' => UploadedFile::fake()->create('favicon.ico', 1, 'image/vnd.microsoft.icon')], $accept)->assertOk();
    expect(Setting::current()->favicon)->toMatch('#^site/[a-z0-9]{32}/original\.ico$#');

    $this->post('/api/v1/settings/logo', ['image' => UploadedFile::fake()->create('logo.ico', 1, 'image/vnd.microsoft.icon')], $accept)->assertUnprocessable();
});
