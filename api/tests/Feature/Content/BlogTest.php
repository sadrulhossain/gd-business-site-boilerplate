<?php

use App\Models\Category;
use App\Models\Menu;
use App\Models\Post;
use Database\Seeders\BlogModuleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('uploads');
    $this->seed(BlogModuleSeeder::class);
});

it('requires authentication and the module permission', function () {
    $this->getJson('/api/v1/posts')->assertUnauthorized();
    $this->getJson('/api/v1/categories')->assertUnauthorized();

    actingAsRole('nobody');
    $this->getJson('/api/v1/posts')->assertForbidden();
    $this->getJson('/api/v1/categories')->assertForbidden();
});

it('serves only published, non-scheduled posts publicly, newest first', function () {
    $news = Category::create(['name' => 'News', 'slug' => 'news']);
    Post::create(['title' => 'Old', 'slug' => 'old', 'is_published' => true, 'published_at' => now()->subDays(2), 'category_id' => $news->id]);
    Post::create(['title' => 'New', 'slug' => 'new', 'is_published' => true, 'published_at' => now()->subDay()]);
    Post::create(['title' => 'Draft', 'slug' => 'draft', 'is_published' => false]);
    Post::create(['title' => 'Later', 'slug' => 'later', 'is_published' => true, 'published_at' => now()->addDay()]);

    $this->getJson('/api/v1/public/posts')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.slug', 'new')
        ->assertJsonPath('data.1.category.slug', 'news');

    $this->getJson('/api/v1/public/posts?category=news')->assertOk()->assertJsonCount(1, 'data');
    $this->getJson('/api/v1/public/posts/old')->assertOk()->assertJsonPath('data.title', 'Old');
    $this->getJson('/api/v1/public/posts/draft')->assertNotFound();
    $this->getJson('/api/v1/public/posts/later')->assertNotFound();
});

it('lists categories publicly', function () {
    Category::create(['name' => 'B', 'slug' => 'b', 'sort_order' => 2]);
    Category::create(['name' => 'A', 'slug' => 'a', 'sort_order' => 1]);

    $this->getJson('/api/v1/public/categories')->assertOk()->assertJsonPath('data.0.slug', 'a');
});

it('sanitises the rich-text body of both languages on create and update', function () {
    actingAsRole('editor', ['posts.create', 'posts.update']);
    $dirty = '<p>Hello <strong>world</strong></p><script>alert(1)</script>'
        .'<a href="javascript:alert(2)" onclick="x()">bad</a> <a href="https://example.com" target="_blank">ok</a>'
        .'<img src="https://example.com/a.png" onerror="y()"><iframe src="https://evil.test"></iframe>';

    $response = $this->postJson('/api/v1/posts', ['title' => 'T', 'slug' => 't', 'body' => $dirty, 'body_2nd' => $dirty])
        ->assertCreated();

    foreach (['body', 'body_2nd'] as $field) {
        $html = $response->json("data.{$field}");
        expect($html)->toContain('<p>Hello <strong>world</strong></p>')
            ->toContain('href="https://example.com"')
            ->not->toContain('<script')
            ->not->toContain('alert(1)')
            ->not->toContain('javascript:')
            ->not->toContain('onclick')
            ->not->toContain('onerror')
            ->not->toContain('<iframe');
    }

    $this->patchJson('/api/v1/posts/'.$response->json('data.id'), ['body' => '<p>x</p><script>1</script>'])
        ->assertOk()
        ->assertJsonPath('data.body', '<p>x</p>');
});

it('stores an empty body as null', function () {
    actingAsRole('editor', ['posts.create']);

    $this->postJson('/api/v1/posts', ['title' => 'T', 'slug' => 't', 'body' => '   '])
        ->assertCreated()
        ->assertJsonPath('data.body', null);
});

it('creates, shows, updates and deletes a post with a category', function () {
    $cat = Category::create(['name' => 'News', 'slug' => 'news']);
    actingAsRole('editor', ['posts.view', 'posts.create', 'posts.update', 'posts.delete']);

    $id = $this->postJson('/api/v1/posts', ['title' => 'T', 'title_2nd' => 'শিরোনাম', 'slug' => 't', 'category_id' => $cat->id])
        ->assertCreated()
        ->assertJsonPath('data.title_2nd', 'শিরোনাম')
        ->assertJsonPath('data.is_published', false)
        ->assertJsonPath('data.category.slug', 'news')
        ->json('data.id');

    $this->getJson("/api/v1/posts/{$id}")->assertOk();
    $this->getJson('/api/v1/posts')->assertOk()->assertJsonCount(1, 'data');
    $this->patchJson("/api/v1/posts/{$id}", ['is_published' => true, 'category_id' => null])
        ->assertOk()
        ->assertJsonPath('data.is_published', true)
        ->assertJsonPath('data.category', null);
    $this->deleteJson("/api/v1/posts/{$id}")->assertNoContent();
    expect(Post::count())->toBe(0);
});

it('keeps posts when their category is deleted', function () {
    $cat = Category::create(['name' => 'News', 'slug' => 'news']);
    $post = Post::create(['title' => 'T', 'slug' => 't', 'category_id' => $cat->id]);
    actingAsRole('editor', ['categories.delete']);

    $this->deleteJson("/api/v1/categories/{$cat->id}")->assertNoContent();
    expect($post->refresh()->category_id)->toBeNull();
});

it('manages categories', function () {
    actingAsRole('editor', ['categories.view', 'categories.create', 'categories.update']);

    $id = $this->postJson('/api/v1/categories', ['name' => 'News', 'name_2nd' => 'খবর', 'slug' => 'news'])
        ->assertCreated()
        ->assertJsonPath('data.sort_order', 0)
        ->json('data.id');

    $this->patchJson("/api/v1/categories/{$id}", ['slug' => 'latest'])->assertOk()->assertJsonPath('data.slug', 'latest');
    $this->getJson("/api/v1/categories/{$id}")->assertOk();
});

it('forbids writes without the matching permission', function () {
    $post = Post::create(['title' => 'T', 'slug' => 't']);
    $cat = Category::create(['name' => 'C', 'slug' => 'c']);
    actingAsRole('viewer', ['posts.view', 'categories.view']);

    $this->postJson('/api/v1/posts', ['title' => 'x', 'slug' => 'x'])->assertForbidden();
    $this->patchJson("/api/v1/posts/{$post->id}", ['title' => 'x'])->assertForbidden();
    $this->deleteJson("/api/v1/posts/{$post->id}")->assertForbidden();
    $this->postJson('/api/v1/categories', ['name' => 'x', 'slug' => 'x'])->assertForbidden();
    $this->patchJson("/api/v1/categories/{$cat->id}", ['name' => 'x'])->assertForbidden();
    $this->deleteJson("/api/v1/categories/{$cat->id}")->assertForbidden();
    expect(Post::count())->toBe(1)->and(Category::count())->toBe(1);
});

it('validates input', function () {
    Post::create(['title' => 'T', 'slug' => 'taken']);
    Category::create(['name' => 'C', 'slug' => 'taken']);
    actingAsRole('editor', ['posts.create', 'posts.update', 'categories.create']);

    $post = $this->postJson('/api/v1/posts', ['slug' => 'Not A Slug', 'category_id' => 999, 'is_published' => 'maybe', 'published_at' => 'soon'])
        ->assertStatus(422);
    expect(array_keys($post->json('error.details')))->toEqualCanonicalizing(['title', 'slug', 'category_id', 'is_published', 'published_at']);

    $dup = $this->postJson('/api/v1/posts', ['title' => 'T', 'slug' => 'taken'])->assertStatus(422);
    expect(array_keys($dup->json('error.details')))->toEqual(['slug']);

    $cat = $this->postJson('/api/v1/categories', ['slug' => 'taken'])->assertStatus(422);
    expect(array_keys($cat->json('error.details')))->toEqualCanonicalizing(['name', 'slug']);
});

it('uploads and removes a post image', function () {
    $post = Post::create(['title' => 'T', 'slug' => 't']);
    actingAsRole('editor', ['posts.update']);

    $this->post("/api/v1/posts/{$post->id}/image", ['image' => UploadedFile::fake()->image('a.jpg', 1000, 600)], ['Accept' => 'application/json'])->assertOk();
    $path = $post->refresh()->image;
    expect($path)->toMatch('#^posts/[a-z0-9]{32}/original\.jpg$#');
    Storage::disk('uploads')->assertExists($path);

    $this->deleteJson("/api/v1/posts/{$post->id}/image")->assertOk()->assertJsonPath('data.image', null);
    expect(Storage::disk('uploads')->allFiles())->toBe([]);

    actingAsRole('viewer', ['posts.view']);
    $this->post("/api/v1/posts/{$post->id}/image", ['image' => UploadedFile::fake()->image('a.jpg')], ['Accept' => 'application/json'])->assertForbidden();
});

it('seeds the menu rows idempotently', function () {
    $this->seed(BlogModuleSeeder::class);

    expect(Menu::where('permission_name', 'posts.view')->count())->toBe(1)
        ->and(Menu::where('permission_name', 'categories.view')->count())->toBe(1);
});
