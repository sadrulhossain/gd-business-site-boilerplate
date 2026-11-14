<?php

use App\Models\Pricing;
use App\Models\Section;
use App\Models\Service;
use Database\Seeders\AboutModuleSeeder;
use Database\Seeders\HeroModuleSeeder;
use Database\Seeders\PricingModuleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed([PricingModuleSeeder::class, HeroModuleSeeder::class, AboutModuleSeeder::class]);
});

function plan(Service $service, array $overrides = []): Pricing
{
    return Pricing::create($overrides + ['service_id' => $service->id, 'name' => 'Basic', 'price' => 10]);
}

it('requires authentication and pricing.view for the admin list', function () {
    $this->getJson('/api/v1/pricing')->assertUnauthorized();

    actingAsRole('nobody');
    $this->getJson('/api/v1/pricing')->assertForbidden();
});

it('creates, updates and deletes a price for a service', function () {
    $service = Service::create(['title' => 'S']);
    actingAsRole('editor', ['pricing.view', 'pricing.create', 'pricing.update', 'pricing.delete']);

    $id = $this->postJson('/api/v1/pricing', ['service_id' => $service->id, 'name' => 'Pro', 'price' => 99, 'features' => "A\nB"])
        ->assertCreated()
        ->assertJsonPath('data.service.id', $service->id)
        ->json('data.id');

    $this->patchJson("/api/v1/pricing/{$id}", ['price' => 120, 'is_featured' => true])->assertOk()->assertJsonPath('data.price', '120.00');
    $this->postJson('/api/v1/pricing', ['name' => 'x'])->assertUnprocessable();
    $this->deleteJson("/api/v1/pricing/{$id}")->assertNoContent();
});

it('serves only visible prices of visible services publicly, filterable by service', function () {
    $a = Service::create(['title' => 'A', 'sort_order' => 1]);
    $b = Service::create(['title' => 'B', 'sort_order' => 2]);
    $hidden = Service::create(['title' => 'Hidden', 'is_visible' => false]);
    plan($b, ['name' => 'B1']);
    plan($a, ['name' => 'A2', 'sort_order' => 2]);
    plan($a, ['name' => 'A1', 'sort_order' => 1]);
    plan($a, ['name' => 'Off', 'is_visible' => false]);
    plan($hidden);

    $this->getJson('/api/v1/public/pricing')->assertOk()->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.name', 'A1')->assertJsonPath('data.2.name', 'B1');
    $this->getJson("/api/v1/public/pricing?service_id={$b->id}")->assertJsonCount(1, 'data');
});

it('manages the hero through its own permissions', function () {
    Storage::fake('uploads');
    $this->getJson('/api/v1/hero')->assertUnauthorized();

    actingAsRole('viewer', ['hero.view']);
    $this->getJson('/api/v1/hero')->assertOk()->assertJsonPath('data.key', 'hero');
    $this->patchJson('/api/v1/hero', ['title' => 'X'])->assertForbidden();

    actingAsRole('editor', ['hero.view', 'hero.update']);
    $this->patchJson('/api/v1/hero', ['title' => 'New title', 'content' => 'Text', 'title_2nd' => 'শিরোনাম'])->assertOk()->assertJsonPath('data.title', 'New title');
    $this->post('/api/v1/hero/image', ['image' => UploadedFile::fake()->image('b.jpg', 1600, 600)], ['Accept' => 'application/json'])->assertOk();
    expect(Section::panel('hero')->image)->toMatch('#^sections/[a-z0-9]{32}/original\.jpg$#');
    $this->deleteJson('/api/v1/hero/image')->assertOk()->assertJsonPath('data.image', null);
});

it('keeps the hero and about permissions apart', function () {
    actingAsRole('hero-only', ['hero.view', 'hero.update']);

    $this->getJson('/api/v1/hero')->assertOk();
    $this->getJson('/api/v1/about')->assertForbidden();

    actingAsRole('about-editor', ['about.view', 'about.update']);
    $this->patchJson('/api/v1/about', ['title' => 'Who we are'])->assertOk()->assertJsonPath('data.key', 'about')->assertJsonPath('data.title', 'Who we are');
});
