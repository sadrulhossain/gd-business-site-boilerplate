<?php

use App\Models\Category;
use App\Models\Faq;
use App\Models\Post;

beforeEach(function () {
    config(['locales.supported' => ['en', 'bn'], 'locales.default' => 'en', 'locales.second' => 'bn']);
    Faq::create(['question' => 'Q', 'question_2nd' => 'প্র', 'answer' => 'A']); // answer_2nd empty
});

it('keeps both languages without ?lang', function () {
    $this->getJson('/api/v1/public/faqs')
        ->assertOk()
        ->assertJsonPath('data.0.question', 'Q')
        ->assertJsonPath('data.0.question_2nd', 'প্র');
});

it('returns the second language under the base keys for ?lang=<second>', function () {
    $response = $this->getJson('/api/v1/public/faqs?lang=bn')->assertOk();

    $response->assertJsonPath('data.0.question', 'প্র')
        ->assertJsonPath('data.0.answer', 'A'); // empty twin falls back to the first language
    expect($response->json('data.0'))->not->toHaveKeys(['question_2nd', 'answer_2nd']);
});

it('returns the first language only for ?lang=<default>', function () {
    $response = $this->getJson('/api/v1/public/faqs?lang=en')->assertOk();

    $response->assertJsonPath('data.0.question', 'Q');
    expect($response->json('data.0'))->not->toHaveKeys(['question_2nd', 'answer_2nd']);
});

it('rejects unsupported languages', function () {
    $response = $this->getJson('/api/v1/public/faqs?lang=xx')->assertStatus(422);

    expect(array_keys($response->json('error.details')))->toEqual(['lang']);
});

it('localises nested and paginated resources', function () {
    $cat = Category::create(['name' => 'News', 'name_2nd' => 'খবর', 'slug' => 'news']);
    Post::create(['title' => 'T', 'title_2nd' => 'শি', 'slug' => 't', 'is_published' => true, 'category_id' => $cat->id]);

    $this->getJson('/api/v1/public/posts?lang=bn')
        ->assertOk()
        ->assertJsonPath('data.0.title', 'শি')
        ->assertJsonPath('data.0.category.name', 'খবর')
        ->assertJsonMissingPath('data.0.title_2nd')
        ->assertJsonMissingPath('data.0.category.name_2nd')
        ->assertJsonPath('meta.current_page', 1);

    $this->getJson('/api/v1/public/posts/t?lang=bn')->assertOk()->assertJsonPath('data.title', 'শি');
});
