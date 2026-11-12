<?php

use App\Models\Menu;
use App\Models\Service;
use Database\Seeders\ServicesModuleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('uploads');
    $this->seed(ServicesModuleSeeder::class);
});

it('requires authentication and services.view for the admin list', function () {
    $this->getJson('/api/v1/services')->assertUnauthorized();

    actingAsRole('nobody');
    $this->getJson('/api/v1/services')->assertForbidden();
});

it('serves only visible Services, in order, to anonymous visitors', function () {
    Service::create(['title' => 'Consulting', 'description' => 'We help.', 'title_2nd' => 'বাংলা', 'sort_order' => 2]);
    Service::create(['title' => 'Consulting', 'description' => 'We help.', 'title_2nd' => 'বাংলা', 'sort_order' => 1, 'title' => 'First']);
    Service::create(['title' => 'Consulting', 'description' => 'We help.', 'title_2nd' => 'বাংলা', 'is_visible' => false]);

    $this->getJson('/api/v1/public/services')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.title', 'First');
});

it('lists hidden Services for the admin and shows one', function () {
    $service = Service::create(['title' => 'Consulting', 'description' => 'We help.', 'title_2nd' => 'বাংলা', 'is_visible' => false]);
    actingAsRole('viewer', ['services.view']);

    $this->getJson('/api/v1/services')->assertOk()->assertJsonCount(1, 'data');
    $this->getJson("/api/v1/services/{$service->id}")->assertOk()->assertJsonPath('data.title', 'Consulting');
});

it('creates a service with both languages', function () {
    actingAsRole('editor', ['services.create']);

    $this->postJson('/api/v1/services', ['title' => 'Consulting', 'description' => 'We help.', 'title_2nd' => 'বাংলা'])
        ->assertCreated()
        ->assertJsonPath('data.title_2nd', 'বাংলা')
        ->assertJsonPath('data.is_visible', true)
        ->assertJsonPath('data.image', null);
});

it('updates and deletes a service', function () {
    $service = Service::create(['title' => 'Consulting', 'description' => 'We help.', 'title_2nd' => 'বাংলা']);
    actingAsRole('editor', ['services.update', 'services.delete']);

    $this->patchJson("/api/v1/services/{$service->id}", ['description' => 'Changed', 'is_visible' => false])
        ->assertOk()
        ->assertJsonPath('data.description', 'Changed');
    expect($service->refresh()->is_visible)->toBeFalse();

    $this->deleteJson("/api/v1/services/{$service->id}")->assertNoContent();
    expect(Service::count())->toBe(0);
});

it('forbids writes without the matching permission', function () {
    $service = Service::create(['title' => 'Consulting', 'description' => 'We help.', 'title_2nd' => 'বাংলা']);
    actingAsRole('viewer', ['services.view']);

    $this->postJson('/api/v1/services', ['title' => 'Consulting', 'description' => 'We help.', 'title_2nd' => 'বাংলা'])->assertForbidden();
    $this->patchJson("/api/v1/services/{$service->id}", ['description' => 'x'])->assertForbidden();
    $this->deleteJson("/api/v1/services/{$service->id}")->assertForbidden();
    expect(Service::count())->toBe(1);
});

it('validates input', function () {
    actingAsRole('editor', ['services.create', 'services.update']);

    $create = $this->postJson('/api/v1/services', ['sort_order' => -1, 'is_visible' => 'maybe'])->assertStatus(422);
    expect(array_keys($create->json('error.details')))->toEqualCanonicalizing(['title', 'sort_order', 'is_visible']);

    $id = Service::create(['title' => 'Consulting', 'description' => 'We help.', 'title_2nd' => 'বাংলা'])->id;
    $update = $this->patchJson("/api/v1/services/{$id}", ['title' => ''])->assertStatus(422);
    expect(array_keys($update->json('error.details')))->toEqual(['title']);
});

it('uploads, replaces and removes the image', function () {
    $service = Service::create(['title' => 'Consulting', 'description' => 'We help.', 'title_2nd' => 'বাংলা']);
    actingAsRole('editor', ['services.update']);
    $upload = fn () => $this->post(
        "/api/v1/services/{$service->id}/image",
        ['image' => UploadedFile::fake()->image('photo.jpg', 1200, 800)],
        ['Accept' => 'application/json'],
    );

    $upload()->assertOk();
    $first = $service->refresh()->image;
    expect($first)->toMatch('#^services/[a-z0-9]{32}/original\.jpg$#');
    Storage::disk('uploads')->assertExists($first);

    $upload()->assertOk();
    Storage::disk('uploads')->assertMissing($first);

    $this->deleteJson("/api/v1/services/{$service->id}/image")->assertOk()->assertJsonPath('data.image', null);
    expect(Storage::disk('uploads')->allFiles())->toBe([]);
});

it('forbids image changes without services.update and rejects non-images', function () {
    $service = Service::create(['title' => 'Consulting', 'description' => 'We help.', 'title_2nd' => 'বাংলা']);
    actingAsRole('viewer', ['services.view']);
    $this->post("/api/v1/services/{$service->id}/image", ['image' => UploadedFile::fake()->image('a.jpg')], ['Accept' => 'application/json'])->assertForbidden();
    $this->deleteJson("/api/v1/services/{$service->id}/image")->assertForbidden();

    actingAsRole('editor', ['services.update']);
    $this->post("/api/v1/services/{$service->id}/image", ['image' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])->assertStatus(422);
    expect(Storage::disk('uploads')->allFiles())->toBe([]);
});

it('deletes the image files together with the service', function () {
    $service = Service::create(['title' => 'Consulting', 'description' => 'We help.', 'title_2nd' => 'বাংলা']);
    actingAsRole('editor', ['services.update', 'services.delete']);
    $this->post("/api/v1/services/{$service->id}/image", ['image' => UploadedFile::fake()->image('a.jpg', 600, 400)], ['Accept' => 'application/json'])->assertOk();

    $this->deleteJson("/api/v1/services/{$service->id}")->assertNoContent();
    expect(Storage::disk('uploads')->allFiles())->toBe([]);
});

it('seeds the menu row idempotently', function () {
    $this->seed(ServicesModuleSeeder::class);

    expect(Menu::where('permission_name', 'services.view')->count())->toBe(1);
});
