<?php

use App\Models\Menu;
use App\Models\TeamMember;
use Database\Seeders\TeamModuleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('uploads');
    $this->seed(TeamModuleSeeder::class);
});

it('requires authentication and team.view for the admin list', function () {
    $this->getJson('/api/v1/team')->assertUnauthorized();

    actingAsRole('nobody');
    $this->getJson('/api/v1/team')->assertForbidden();
});

it('serves only visible team members, in order, to anonymous visitors', function () {
    TeamMember::create(['name' => 'John Smith', 'role' => 'Designer', 'bio' => 'Bio.', 'role_2nd' => 'বাংলা', 'sort_order' => 2]);
    TeamMember::create(['name' => 'John Smith', 'role' => 'Designer', 'bio' => 'Bio.', 'role_2nd' => 'বাংলা', 'sort_order' => 1, 'name' => 'First']);
    TeamMember::create(['name' => 'John Smith', 'role' => 'Designer', 'bio' => 'Bio.', 'role_2nd' => 'বাংলা', 'is_visible' => false]);

    $this->getJson('/api/v1/public/team')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'First');
});

it('lists hidden team members for the admin and shows one', function () {
    $teamMember = TeamMember::create(['name' => 'John Smith', 'role' => 'Designer', 'bio' => 'Bio.', 'role_2nd' => 'বাংলা', 'is_visible' => false]);
    actingAsRole('viewer', ['team.view']);

    $this->getJson('/api/v1/team')->assertOk()->assertJsonCount(1, 'data');
    $this->getJson("/api/v1/team/{$teamMember->id}")->assertOk()->assertJsonPath('data.name', 'John Smith');
});

it('creates a team member with both languages', function () {
    actingAsRole('editor', ['team.create']);

    $this->postJson('/api/v1/team', ['name' => 'John Smith', 'role' => 'Designer', 'bio' => 'Bio.', 'role_2nd' => 'বাংলা'])
        ->assertCreated()
        ->assertJsonPath('data.role_2nd', 'বাংলা')
        ->assertJsonPath('data.is_visible', true)
        ->assertJsonPath('data.image', null);
});

it('updates and deletes a team member', function () {
    $teamMember = TeamMember::create(['name' => 'John Smith', 'role' => 'Designer', 'bio' => 'Bio.', 'role_2nd' => 'বাংলা']);
    actingAsRole('editor', ['team.update', 'team.delete']);

    $this->patchJson("/api/v1/team/{$teamMember->id}", ['bio' => 'Changed', 'is_visible' => false])
        ->assertOk()
        ->assertJsonPath('data.bio', 'Changed');
    expect($teamMember->refresh()->is_visible)->toBeFalse();

    $this->deleteJson("/api/v1/team/{$teamMember->id}")->assertNoContent();
    expect(TeamMember::count())->toBe(0);
});

it('forbids writes without the matching permission', function () {
    $teamMember = TeamMember::create(['name' => 'John Smith', 'role' => 'Designer', 'bio' => 'Bio.', 'role_2nd' => 'বাংলা']);
    actingAsRole('viewer', ['team.view']);

    $this->postJson('/api/v1/team', ['name' => 'John Smith', 'role' => 'Designer', 'bio' => 'Bio.', 'role_2nd' => 'বাংলা'])->assertForbidden();
    $this->patchJson("/api/v1/team/{$teamMember->id}", ['bio' => 'x'])->assertForbidden();
    $this->deleteJson("/api/v1/team/{$teamMember->id}")->assertForbidden();
    expect(TeamMember::count())->toBe(1);
});

it('validates input', function () {
    actingAsRole('editor', ['team.create', 'team.update']);

    $create = $this->postJson('/api/v1/team', ['sort_order' => -1, 'is_visible' => 'maybe'])->assertStatus(422);
    expect(array_keys($create->json('error.details')))->toEqualCanonicalizing(['name', 'role', 'sort_order', 'is_visible']);

    $id = TeamMember::create(['name' => 'John Smith', 'role' => 'Designer', 'bio' => 'Bio.', 'role_2nd' => 'বাংলা'])->id;
    $update = $this->patchJson("/api/v1/team/{$id}", ['name' => ''])->assertStatus(422);
    expect(array_keys($update->json('error.details')))->toEqual(['name']);
});

it('uploads, replaces and removes the image', function () {
    $teamMember = TeamMember::create(['name' => 'John Smith', 'role' => 'Designer', 'bio' => 'Bio.', 'role_2nd' => 'বাংলা']);
    actingAsRole('editor', ['team.update']);
    $upload = fn () => $this->post(
        "/api/v1/team/{$teamMember->id}/image",
        ['image' => UploadedFile::fake()->image('photo.jpg', 1200, 800)],
        ['Accept' => 'application/json'],
    );

    $upload()->assertOk();
    $first = $teamMember->refresh()->image;
    expect($first)->toMatch('#^team/[a-z0-9]{32}/original\.jpg$#');
    Storage::disk('uploads')->assertExists($first);

    $upload()->assertOk();
    Storage::disk('uploads')->assertMissing($first);

    $this->deleteJson("/api/v1/team/{$teamMember->id}/image")->assertOk()->assertJsonPath('data.image', null);
    expect(Storage::disk('uploads')->allFiles())->toBe([]);
});

it('forbids image changes without team.update and rejects non-images', function () {
    $teamMember = TeamMember::create(['name' => 'John Smith', 'role' => 'Designer', 'bio' => 'Bio.', 'role_2nd' => 'বাংলা']);
    actingAsRole('viewer', ['team.view']);
    $this->post("/api/v1/team/{$teamMember->id}/image", ['image' => UploadedFile::fake()->image('a.jpg')], ['Accept' => 'application/json'])->assertForbidden();
    $this->deleteJson("/api/v1/team/{$teamMember->id}/image")->assertForbidden();

    actingAsRole('editor', ['team.update']);
    $this->post("/api/v1/team/{$teamMember->id}/image", ['image' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])->assertStatus(422);
    expect(Storage::disk('uploads')->allFiles())->toBe([]);
});

it('deletes the image files together with the team member', function () {
    $teamMember = TeamMember::create(['name' => 'John Smith', 'role' => 'Designer', 'bio' => 'Bio.', 'role_2nd' => 'বাংলা']);
    actingAsRole('editor', ['team.update', 'team.delete']);
    $this->post("/api/v1/team/{$teamMember->id}/image", ['image' => UploadedFile::fake()->image('a.jpg', 600, 400)], ['Accept' => 'application/json'])->assertOk();

    $this->deleteJson("/api/v1/team/{$teamMember->id}")->assertNoContent();
    expect(Storage::disk('uploads')->allFiles())->toBe([]);
});

it('seeds the menu row idempotently', function () {
    $this->seed(TeamModuleSeeder::class);

    expect(Menu::where('permission_name', 'team.view')->count())->toBe(1);
});
