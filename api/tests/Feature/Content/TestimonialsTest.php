<?php

use App\Models\Menu;
use App\Models\Testimonial;
use Database\Seeders\TestimonialsModuleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('uploads');
    $this->seed(TestimonialsModuleSeeder::class);
});

it('requires authentication and testimonials.view for the admin list', function () {
    $this->getJson('/api/v1/testimonials')->assertUnauthorized();

    actingAsRole('nobody');
    $this->getJson('/api/v1/testimonials')->assertForbidden();
});

it('serves only visible Testimonials, in order, to anonymous visitors', function () {
    Testimonial::create(['author_name' => 'Jane Doe', 'author_role' => 'CEO', 'quote' => 'Great work.', 'author_role_2nd' => 'বাংলা', 'sort_order' => 2]);
    Testimonial::create(['author_name' => 'Jane Doe', 'author_role' => 'CEO', 'quote' => 'Great work.', 'author_role_2nd' => 'বাংলা', 'sort_order' => 1, 'author_name' => 'First']);
    Testimonial::create(['author_name' => 'Jane Doe', 'author_role' => 'CEO', 'quote' => 'Great work.', 'author_role_2nd' => 'বাংলা', 'is_visible' => false]);

    $this->getJson('/api/v1/public/testimonials')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.author_name', 'First');
});

it('lists hidden Testimonials for the admin and shows one', function () {
    $testimonial = Testimonial::create(['author_name' => 'Jane Doe', 'author_role' => 'CEO', 'quote' => 'Great work.', 'author_role_2nd' => 'বাংলা', 'is_visible' => false]);
    actingAsRole('viewer', ['testimonials.view']);

    $this->getJson('/api/v1/testimonials')->assertOk()->assertJsonCount(1, 'data');
    $this->getJson("/api/v1/testimonials/{$testimonial->id}")->assertOk()->assertJsonPath('data.author_name', 'Jane Doe');
});

it('creates a testimonial with both languages', function () {
    actingAsRole('editor', ['testimonials.create']);

    $this->postJson('/api/v1/testimonials', ['author_name' => 'Jane Doe', 'author_role' => 'CEO', 'quote' => 'Great work.', 'author_role_2nd' => 'বাংলা'])
        ->assertCreated()
        ->assertJsonPath('data.author_role_2nd', 'বাংলা')
        ->assertJsonPath('data.is_visible', true)
        ->assertJsonPath('data.image', null);
});

it('updates and deletes a testimonial', function () {
    $testimonial = Testimonial::create(['author_name' => 'Jane Doe', 'author_role' => 'CEO', 'quote' => 'Great work.', 'author_role_2nd' => 'বাংলা']);
    actingAsRole('editor', ['testimonials.update', 'testimonials.delete']);

    $this->patchJson("/api/v1/testimonials/{$testimonial->id}", ['quote' => 'Changed', 'is_visible' => false])
        ->assertOk()
        ->assertJsonPath('data.quote', 'Changed');
    expect($testimonial->refresh()->is_visible)->toBeFalse();

    $this->deleteJson("/api/v1/testimonials/{$testimonial->id}")->assertNoContent();
    expect(Testimonial::count())->toBe(0);
});

it('forbids writes without the matching permission', function () {
    $testimonial = Testimonial::create(['author_name' => 'Jane Doe', 'author_role' => 'CEO', 'quote' => 'Great work.', 'author_role_2nd' => 'বাংলা']);
    actingAsRole('viewer', ['testimonials.view']);

    $this->postJson('/api/v1/testimonials', ['author_name' => 'Jane Doe', 'author_role' => 'CEO', 'quote' => 'Great work.', 'author_role_2nd' => 'বাংলা'])->assertForbidden();
    $this->patchJson("/api/v1/testimonials/{$testimonial->id}", ['quote' => 'x'])->assertForbidden();
    $this->deleteJson("/api/v1/testimonials/{$testimonial->id}")->assertForbidden();
    expect(Testimonial::count())->toBe(1);
});

it('validates input', function () {
    actingAsRole('editor', ['testimonials.create', 'testimonials.update']);

    $create = $this->postJson('/api/v1/testimonials', ['sort_order' => -1, 'is_visible' => 'maybe'])->assertStatus(422);
    expect(array_keys($create->json('error.details')))->toEqualCanonicalizing(['author_name', 'quote', 'sort_order', 'is_visible']);

    $id = Testimonial::create(['author_name' => 'Jane Doe', 'author_role' => 'CEO', 'quote' => 'Great work.', 'author_role_2nd' => 'বাংলা'])->id;
    $update = $this->patchJson("/api/v1/testimonials/{$id}", ['author_name' => ''])->assertStatus(422);
    expect(array_keys($update->json('error.details')))->toEqual(['author_name']);
});

it('uploads, replaces and removes the image', function () {
    $testimonial = Testimonial::create(['author_name' => 'Jane Doe', 'author_role' => 'CEO', 'quote' => 'Great work.', 'author_role_2nd' => 'বাংলা']);
    actingAsRole('editor', ['testimonials.update']);
    $upload = fn () => $this->post(
        "/api/v1/testimonials/{$testimonial->id}/image",
        ['image' => UploadedFile::fake()->image('photo.jpg', 1200, 800)],
        ['Accept' => 'application/json'],
    );

    $upload()->assertOk();
    $first = $testimonial->refresh()->image;
    expect($first)->toMatch('#^testimonials/[a-z0-9]{32}/original\.jpg$#');
    Storage::disk('uploads')->assertExists($first);

    $upload()->assertOk();
    Storage::disk('uploads')->assertMissing($first);

    $this->deleteJson("/api/v1/testimonials/{$testimonial->id}/image")->assertOk()->assertJsonPath('data.image', null);
    expect(Storage::disk('uploads')->allFiles())->toBe([]);
});

it('forbids image changes without testimonials.update and rejects non-images', function () {
    $testimonial = Testimonial::create(['author_name' => 'Jane Doe', 'author_role' => 'CEO', 'quote' => 'Great work.', 'author_role_2nd' => 'বাংলা']);
    actingAsRole('viewer', ['testimonials.view']);
    $this->post("/api/v1/testimonials/{$testimonial->id}/image", ['image' => UploadedFile::fake()->image('a.jpg')], ['Accept' => 'application/json'])->assertForbidden();
    $this->deleteJson("/api/v1/testimonials/{$testimonial->id}/image")->assertForbidden();

    actingAsRole('editor', ['testimonials.update']);
    $this->post("/api/v1/testimonials/{$testimonial->id}/image", ['image' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])->assertStatus(422);
    expect(Storage::disk('uploads')->allFiles())->toBe([]);
});

it('deletes the image files together with the testimonial', function () {
    $testimonial = Testimonial::create(['author_name' => 'Jane Doe', 'author_role' => 'CEO', 'quote' => 'Great work.', 'author_role_2nd' => 'বাংলা']);
    actingAsRole('editor', ['testimonials.update', 'testimonials.delete']);
    $this->post("/api/v1/testimonials/{$testimonial->id}/image", ['image' => UploadedFile::fake()->image('a.jpg', 600, 400)], ['Accept' => 'application/json'])->assertOk();

    $this->deleteJson("/api/v1/testimonials/{$testimonial->id}")->assertNoContent();
    expect(Storage::disk('uploads')->allFiles())->toBe([]);
});

it('seeds the menu row idempotently', function () {
    $this->seed(TestimonialsModuleSeeder::class);

    expect(Menu::where('permission_name', 'testimonials.view')->count())->toBe(1);
});
