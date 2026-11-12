<?php

use App\Models\ContactMessage;
use App\Models\Menu;
use App\Models\Setting;
use App\Notifications\NewContactMessage;
use Database\Seeders\ContactModuleSeeder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(ContactModuleSeeder::class);

    // Fetches a captcha and returns its solved fields.
    $this->solved = function () {
        $data = $this->getJson('/api/v1/public/captcha')->assertOk()->json('data');
        [$a, $b] = explode(' + ', $data['question']);

        return ['captcha_id' => $data['id'], 'captcha_answer' => (int) $a + (int) $b];
    };
    $this->form = fn () => ['name' => 'Jane', 'email' => 'jane@example.com', 'message' => 'Hello there'] + ($this->solved)();
});

it('stores a message and notifies the site owner by queued mail', function () {
    Notification::fake();
    Setting::current()->update(['email' => 'owner@example.com']);

    $this->postJson('/api/v1/contact', ($this->form)() + ['phone' => '123', 'subject' => 'Hi'])
        ->assertCreated()
        ->assertExactJson(['data' => ['received' => true]]);

    expect(ContactMessage::count())->toBe(1)->and(ContactMessage::first()->read_at)->toBeNull();
    Notification::assertSentOnDemand(NewContactMessage::class, fn ($n, $channels, $notifiable) => $notifiable->routes['mail'] === 'owner@example.com');
    expect(new NewContactMessage(ContactMessage::first()))->toBeInstanceOf(ShouldQueue::class);
});

it('falls back to CONTACT_NOTIFY_TO and skips mail when no recipient is known', function () {
    Notification::fake();
    Setting::current()->update(['email' => null]);

    config(['contact.notify_to' => null]);
    $this->postJson('/api/v1/contact', ($this->form)())->assertCreated();
    Notification::assertNothingSent();

    config(['contact.notify_to' => 'fallback@example.com']);
    $this->postJson('/api/v1/contact', ($this->form)())->assertCreated();
    Notification::assertSentOnDemand(NewContactMessage::class, fn ($n, $c, $notifiable) => $notifiable->routes['mail'] === 'fallback@example.com');
});

it('rejects a wrong captcha answer', function () {
    $form = ($this->form)();
    $form['captcha_answer']++;

    $response = $this->postJson('/api/v1/contact', $form)->assertStatus(422);

    expect(array_keys($response->json('error.details')))->toEqual(['captcha_answer'])
        ->and(ContactMessage::count())->toBe(0);
});

it('accepts each captcha only once', function () {
    $form = ($this->form)();

    $this->postJson('/api/v1/contact', $form)->assertCreated();
    $this->postJson('/api/v1/contact', $form)->assertStatus(422)->assertJsonPath('error.details.captcha_answer.0', fn ($m) => is_string($m));

    expect(ContactMessage::count())->toBe(1);
});

it('consumes the captcha even when other fields are invalid', function () {
    $captcha = ($this->solved)();

    $this->postJson('/api/v1/contact', ['email' => 'nope'] + $captcha)->assertStatus(422);
    $this->postJson('/api/v1/contact', ['name' => 'J', 'email' => 'j@example.com', 'message' => 'm'] + $captcha)
        ->assertStatus(422); // same captcha id: already burnt

    expect(ContactMessage::count())->toBe(0);
});

it('validates input and requires the captcha fields', function () {
    $response = $this->postJson('/api/v1/contact', [])->assertStatus(422);

    expect(array_keys($response->json('error.details')))
        ->toEqualCanonicalizing(['name', 'email', 'message', 'captcha_id', 'captcha_answer']);
});

it('rejects submissions that fill the honeypot', function () {
    $response = $this->postJson('/api/v1/contact', ($this->form)() + ['website' => 'http://spam.test'])->assertStatus(422);

    expect(array_keys($response->json('error.details')))->toEqual(['website'])
        ->and(ContactMessage::count())->toBe(0);
});

it('throttles submissions per IP', function () {
    config(['contact.per_minute' => 2]);

    $this->postJson('/api/v1/contact', [])->assertStatus(422);
    $this->postJson('/api/v1/contact', [])->assertStatus(422);
    $this->postJson('/api/v1/contact', [])->assertStatus(429);
});

it('requires authentication and contact.view for the inbox', function () {
    $message = ContactMessage::create(['name' => 'J', 'email' => 'j@example.com', 'message' => 'm']);

    $this->getJson('/api/v1/contact-messages')->assertUnauthorized();
    $this->postJson("/api/v1/contact-messages/{$message->id}/read")->assertUnauthorized();

    actingAsRole('nobody');
    $this->getJson('/api/v1/contact-messages')->assertForbidden();
    $this->getJson("/api/v1/contact-messages/{$message->id}")->assertForbidden();
});

it('lists, filters unread and shows messages', function () {
    ContactMessage::create(['name' => 'Read', 'email' => 'a@example.com', 'message' => 'm', 'read_at' => now()]);
    $unread = ContactMessage::create(['name' => 'Unread', 'email' => 'b@example.com', 'message' => 'm']);
    actingAsRole('viewer', ['contact.view']);

    $this->getJson('/api/v1/contact-messages')->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('data.0.name', 'Unread');
    $this->getJson('/api/v1/contact-messages?unread=1')->assertOk()->assertJsonCount(1, 'data');
    $this->getJson("/api/v1/contact-messages/{$unread->id}")->assertOk()->assertJsonPath('data.email', 'b@example.com');
});

it('marks a message read once and needs contact.update', function () {
    $message = ContactMessage::create(['name' => 'J', 'email' => 'j@example.com', 'message' => 'm']);

    actingAsRole('viewer', ['contact.view']);
    $this->postJson("/api/v1/contact-messages/{$message->id}/read")->assertForbidden();
    expect($message->refresh()->read_at)->toBeNull();

    actingAsRole('editor', ['contact.update']);
    $this->postJson("/api/v1/contact-messages/{$message->id}/read")->assertOk();
    $first = $message->refresh()->read_at;
    expect($first)->not->toBeNull();

    $this->travel(5)->minutes();
    $this->postJson("/api/v1/contact-messages/{$message->id}/read")->assertOk();
    expect($message->refresh()->read_at->equalTo($first))->toBeTrue();
});

it('has no create or delete for admins', function () {
    $message = ContactMessage::create(['name' => 'J', 'email' => 'j@example.com', 'message' => 'm']);
    actingAsRole('editor', ['contact.view', 'contact.update']);

    $this->postJson('/api/v1/contact-messages', [])->assertStatus(405);
    $this->deleteJson("/api/v1/contact-messages/{$message->id}")->assertStatus(405);
});

it('seeds the menu row idempotently', function () {
    $this->seed(ContactModuleSeeder::class);

    expect(Menu::where('permission_name', 'contact.view')->count())->toBe(1);
});
