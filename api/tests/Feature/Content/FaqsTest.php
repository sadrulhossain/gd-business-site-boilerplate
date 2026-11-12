<?php

use App\Models\Faq;
use App\Models\Menu;
use Database\Seeders\FaqsModuleSeeder;

beforeEach(fn () => $this->seed(FaqsModuleSeeder::class));

it('requires authentication and faqs.view for the admin list', function () {
    $this->getJson('/api/v1/faqs')->assertUnauthorized();

    actingAsRole('nobody');
    $this->getJson('/api/v1/faqs')->assertForbidden();
});

it('serves only visible FAQs, in order, to anonymous visitors', function () {
    Faq::create(['question' => 'B', 'answer' => 'b', 'sort_order' => 2]);
    Faq::create(['question' => 'A', 'answer' => 'a', 'sort_order' => 1]);
    Faq::create(['question' => 'Hidden', 'answer' => 'h', 'is_visible' => false]);

    $this->getJson('/api/v1/public/faqs')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.question', 'A');
});

it('lists hidden FAQs for the admin and shows one', function () {
    $faq = Faq::create(['question' => 'Hidden', 'answer' => 'h', 'is_visible' => false]);
    actingAsRole('viewer', ['faqs.view']);

    $this->getJson('/api/v1/faqs')->assertOk()->assertJsonCount(1, 'data');
    $this->getJson("/api/v1/faqs/{$faq->id}")->assertOk()->assertJsonPath('data.question', 'Hidden');
});

it('creates a bilingual FAQ', function () {
    actingAsRole('editor', ['faqs.create']);

    $this->postJson('/api/v1/faqs', ['question' => 'Q?', 'question_2nd' => 'প্র?', 'answer' => 'A.'])
        ->assertCreated()
        ->assertJsonPath('data.question_2nd', 'প্র?')
        ->assertJsonPath('data.is_visible', true);
});

it('updates and deletes a FAQ', function () {
    $faq = Faq::create(['question' => 'Q', 'answer' => 'A']);
    actingAsRole('editor', ['faqs.update', 'faqs.delete']);

    $this->patchJson("/api/v1/faqs/{$faq->id}", ['answer' => 'New', 'is_visible' => false])
        ->assertOk()
        ->assertJsonPath('data.answer', 'New');
    expect($faq->refresh()->is_visible)->toBeFalse();

    $this->deleteJson("/api/v1/faqs/{$faq->id}")->assertNoContent();
    expect(Faq::count())->toBe(0);
});

it('forbids writes without the matching permission', function () {
    $faq = Faq::create(['question' => 'Q', 'answer' => 'A']);
    actingAsRole('viewer', ['faqs.view']);

    $this->postJson('/api/v1/faqs', ['question' => 'x', 'answer' => 'y'])->assertForbidden();
    $this->patchJson("/api/v1/faqs/{$faq->id}", ['answer' => 'x'])->assertForbidden();
    $this->deleteJson("/api/v1/faqs/{$faq->id}")->assertForbidden();
    expect(Faq::count())->toBe(1);
});

it('validates input', function () {
    actingAsRole('editor', ['faqs.create', 'faqs.update']);

    $create = $this->postJson('/api/v1/faqs', ['sort_order' => -1, 'is_visible' => 'maybe'])->assertStatus(422);
    expect(array_keys($create->json('error.details')))->toEqualCanonicalizing(['question', 'answer', 'sort_order', 'is_visible']);

    $id = Faq::create(['question' => 'Q', 'answer' => 'A'])->id;
    $update = $this->patchJson("/api/v1/faqs/{$id}", ['question' => ''])->assertStatus(422);
    expect(array_keys($update->json('error.details')))->toEqual(['question']);
});

it('seeds the menu row idempotently', function () {
    $this->seed(FaqsModuleSeeder::class);

    expect(Menu::where('permission_name', 'faqs.view')->count())->toBe(1);
});
