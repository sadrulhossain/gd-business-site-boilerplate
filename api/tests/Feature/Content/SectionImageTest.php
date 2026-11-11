<?php

use App\Jobs\GenerateWebpVariants;
use App\Models\Section;
use Database\Seeders\SectionsModuleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('uploads');
    $this->seed(SectionsModuleSeeder::class);
    $this->section = Section::where('key', 'hero')->firstOrFail();
});

function uploadImage($test, Section $section, ?UploadedFile $file = null)
{
    return $test->post(
        "/api/v1/sections/{$section->id}/image",
        ['image' => $file ?? UploadedFile::fake()->image('photo.jpg', 1200, 800)],
        ['Accept' => 'application/json'],
    );
}

it('stores the upload under a random name and queues the variants', function () {
    Queue::fake();
    actingAsRole('editor', ['sections.update']);

    uploadImage($this, $this->section)->assertOk();

    $path = $this->section->refresh()->image;
    expect($path)->toMatch('#^sections/[a-z0-9]{32}/original\.jpg$#');
    Storage::disk('uploads')->assertExists($path);
    Queue::assertPushed(GenerateWebpVariants::class, fn ($job) => $job->path === $path);
});

it('creates webp variants without upscaling', function () {
    actingAsRole('editor', ['sections.update']); // queue is sync in tests

    $response = uploadImage($this, $this->section)->assertOk();

    $dir = dirname($this->section->refresh()->image);
    foreach ([480, 960, 1200] as $width) {
        Storage::disk('uploads')->assertExists("{$dir}/{$width}.webp");
    }
    Storage::disk('uploads')->assertMissing("{$dir}/1600.webp");
    expect(array_keys((array) $response->json('data.image.webp')))->toEqual(['480', '960', '1200']);
});

it('rejects files that are not allowed images, whatever their name says', function () {
    actingAsRole('editor', ['sections.update']);

    uploadImage($this, $this->section, UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'))->assertStatus(422);
    uploadImage($this, $this->section, UploadedFile::fake()->create('fake.jpg', 10, 'image/jpeg'))->assertStatus(422); // not a real image

    expect($this->section->refresh()->image)->toBeNull();
});

it('rejects files over UPLOADS_MAX_MB', function () {
    config(['uploads.max_mb' => 1]);
    actingAsRole('editor', ['sections.update']);

    uploadImage($this, $this->section, UploadedFile::fake()->image('big.jpg', 600, 400)->size(2048))->assertStatus(422);
});

it('replaces and removes the image together with its variants', function () {
    actingAsRole('editor', ['sections.update']);

    uploadImage($this, $this->section)->assertOk();
    $first = dirname($this->section->refresh()->image);

    uploadImage($this, $this->section)->assertOk();
    $second = dirname($this->section->refresh()->image);

    expect($second)->not->toBe($first);
    Storage::disk('uploads')->assertMissing($first);
    Storage::disk('uploads')->assertExists($second);

    $this->deleteJson("/api/v1/sections/{$this->section->id}/image")->assertOk()->assertJsonPath('data.image', null);
    Storage::disk('uploads')->assertMissing($second);
    expect($this->section->refresh()->image)->toBeNull();
});

it('forbids uploads without sections.update', function () {
    actingAsRole('viewer', ['sections.view']);

    uploadImage($this, $this->section)->assertForbidden();
    $this->deleteJson("/api/v1/sections/{$this->section->id}/image")->assertForbidden();
    expect(Storage::disk('uploads')->allFiles())->toBe([]);
});

it('skips variants when the original is gone', function () {
    (new GenerateWebpVariants('sections/gone/original.jpg'))->handle();

    expect(Storage::disk('uploads')->allFiles())->toBe([]);
});
