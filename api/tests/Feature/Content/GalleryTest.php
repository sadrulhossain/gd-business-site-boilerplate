<?php

use App\Models\GalleryItem;
use App\Models\Menu;
use Database\Seeders\GalleryModuleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('uploads');
    $this->seed(GalleryModuleSeeder::class);
});

it('requires authentication and gallery.view for the admin list', function () {
    $this->getJson('/api/v1/gallery')->assertUnauthorized();

    actingAsRole('nobody');
    $this->getJson('/api/v1/gallery')->assertForbidden();
});

it('serves only visible gallery items, in order, to anonymous visitors', function () {
    GalleryItem::create(['title' => 'Office', 'caption' => 'Our office.', 'title_2nd' => 'বাংলা', 'sort_order' => 2]);
    GalleryItem::create(['title' => 'Office', 'caption' => 'Our office.', 'title_2nd' => 'বাংলা', 'sort_order' => 1, 'title' => 'First']);
    GalleryItem::create(['title' => 'Office', 'caption' => 'Our office.', 'title_2nd' => 'বাংলা', 'is_visible' => false]);

    $this->getJson('/api/v1/public/gallery')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.title', 'First');
});

it('lists hidden gallery items for the admin and shows one', function () {
    $galleryItem = GalleryItem::create(['title' => 'Office', 'caption' => 'Our office.', 'title_2nd' => 'বাংলা', 'is_visible' => false]);
    actingAsRole('viewer', ['gallery.view']);

    $this->getJson('/api/v1/gallery')->assertOk()->assertJsonCount(1, 'data');
    $this->getJson("/api/v1/gallery/{$galleryItem->id}")->assertOk()->assertJsonPath('data.title', 'Office');
});

it('creates a gallery item with both languages', function () {
    actingAsRole('editor', ['gallery.create']);

    $this->postJson('/api/v1/gallery', ['title' => 'Office', 'caption' => 'Our office.', 'title_2nd' => 'বাংলা'])
        ->assertCreated()
        ->assertJsonPath('data.title_2nd', 'বাংলা')
        ->assertJsonPath('data.is_visible', true)
        ->assertJsonPath('data.image', null);
});

it('updates and deletes a gallery item', function () {
    $galleryItem = GalleryItem::create(['title' => 'Office', 'caption' => 'Our office.', 'title_2nd' => 'বাংলা']);
    actingAsRole('editor', ['gallery.update', 'gallery.delete']);

    $this->patchJson("/api/v1/gallery/{$galleryItem->id}", ['caption' => 'Changed', 'is_visible' => false])
        ->assertOk()
        ->assertJsonPath('data.caption', 'Changed');
    expect($galleryItem->refresh()->is_visible)->toBeFalse();

    $this->deleteJson("/api/v1/gallery/{$galleryItem->id}")->assertNoContent();
    expect(GalleryItem::count())->toBe(0);
});

it('forbids writes without the matching permission', function () {
    $galleryItem = GalleryItem::create(['title' => 'Office', 'caption' => 'Our office.', 'title_2nd' => 'বাংলা']);
    actingAsRole('viewer', ['gallery.view']);

    $this->postJson('/api/v1/gallery', ['title' => 'Office', 'caption' => 'Our office.', 'title_2nd' => 'বাংলা'])->assertForbidden();
    $this->patchJson("/api/v1/gallery/{$galleryItem->id}", ['caption' => 'x'])->assertForbidden();
    $this->deleteJson("/api/v1/gallery/{$galleryItem->id}")->assertForbidden();
    expect(GalleryItem::count())->toBe(1);
});

it('validates input', function () {
    actingAsRole('editor', ['gallery.create', 'gallery.update']);

    $create = $this->postJson('/api/v1/gallery', ['sort_order' => -1, 'is_visible' => 'maybe'])->assertStatus(422);
    expect(array_keys($create->json('error.details')))->toEqualCanonicalizing(['sort_order', 'is_visible']);

    $id = GalleryItem::create(['title' => 'Office', 'caption' => 'Our office.', 'title_2nd' => 'বাংলা'])->id;
    $update = $this->patchJson("/api/v1/gallery/{$id}", ['title' => str_repeat('x', 151)])->assertStatus(422);
    expect(array_keys($update->json('error.details')))->toEqual(['title']);
});

it('uploads, replaces and removes the image', function () {
    $galleryItem = GalleryItem::create(['title' => 'Office', 'caption' => 'Our office.', 'title_2nd' => 'বাংলা']);
    actingAsRole('editor', ['gallery.update']);
    $upload = fn () => $this->post(
        "/api/v1/gallery/{$galleryItem->id}/image",
        ['image' => UploadedFile::fake()->image('photo.jpg', 1200, 800)],
        ['Accept' => 'application/json'],
    );

    $upload()->assertOk();
    $first = $galleryItem->refresh()->image;
    expect($first)->toMatch('#^gallery/[a-z0-9]{32}/original\.jpg$#');
    Storage::disk('uploads')->assertExists($first);

    $upload()->assertOk();
    Storage::disk('uploads')->assertMissing($first);

    $this->deleteJson("/api/v1/gallery/{$galleryItem->id}/image")->assertOk()->assertJsonPath('data.image', null);
    expect(Storage::disk('uploads')->allFiles())->toBe([]);
});

it('forbids image changes without gallery.update and rejects non-images', function () {
    $galleryItem = GalleryItem::create(['title' => 'Office', 'caption' => 'Our office.', 'title_2nd' => 'বাংলা']);
    actingAsRole('viewer', ['gallery.view']);
    $this->post("/api/v1/gallery/{$galleryItem->id}/image", ['image' => UploadedFile::fake()->image('a.jpg')], ['Accept' => 'application/json'])->assertForbidden();
    $this->deleteJson("/api/v1/gallery/{$galleryItem->id}/image")->assertForbidden();

    actingAsRole('editor', ['gallery.update']);
    $this->post("/api/v1/gallery/{$galleryItem->id}/image", ['image' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])->assertStatus(422);
    expect(Storage::disk('uploads')->allFiles())->toBe([]);
});

it('deletes the image files together with the gallery item', function () {
    $galleryItem = GalleryItem::create(['title' => 'Office', 'caption' => 'Our office.', 'title_2nd' => 'বাংলা']);
    actingAsRole('editor', ['gallery.update', 'gallery.delete']);
    $this->post("/api/v1/gallery/{$galleryItem->id}/image", ['image' => UploadedFile::fake()->image('a.jpg', 600, 400)], ['Accept' => 'application/json'])->assertOk();

    $this->deleteJson("/api/v1/gallery/{$galleryItem->id}")->assertNoContent();
    expect(Storage::disk('uploads')->allFiles())->toBe([]);
});

it('seeds the menu row idempotently', function () {
    $this->seed(GalleryModuleSeeder::class);

    expect(Menu::where('permission_name', 'gallery.view')->count())->toBe(1);
});
