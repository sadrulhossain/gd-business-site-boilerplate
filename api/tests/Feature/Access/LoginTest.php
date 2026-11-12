<?php

use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\User;
use Database\Seeders\DashboardModuleSeeder;
use Illuminate\Support\Facades\Auth;

it('shows the login page', function () {
    $this->get('/login')->assertOk()->assertSee('Sign in')->assertSee('name="password"', false);
});

it('keeps the JSON 401 for API routes', function () {
    $this->getJson('/api/v1/me')->assertUnauthorized()->assertJsonPath('error.code', 'unauthenticated');
});

it('rejects wrong credentials without revealing which part was wrong', function () {
    $user = User::factory()->create(['password' => 'secret-pass']);

    $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'nope'])
        ->assertRedirect('/login')
        ->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);

    expect(Auth::guard('web')->check())->toBeFalse();
});

it('validates the login form', function () {
    $this->from('/login')->post('/login', [])->assertSessionHasErrors(['email', 'password']);
});

it('throttles repeated login attempts', function () {
    $attempt = fn () => $this->post('/login', ['email' => 'a@example.com', 'password' => 'wrong']);

    foreach (range(1, 10) as $_) {
        $attempt()->assertStatus(302);
    }

    $attempt()->assertStatus(429);
});

it('ends the browser session and returns to the admin signed-out page', function () {
    config(['access.oauth_admin_client.redirect_uri' => 'http://admin.example.test:28080/auth/callback']);
    $this->actingAs(User::factory()->create(), 'web');

    $this->get('/logout')->assertRedirect('http://admin.example.test:28080/signed-out');

    expect(Auth::guard('web')->check())->toBeFalse();
});

it('requires authentication to log out', function () {
    $this->postJson('/api/v1/logout')->assertUnauthorized();
});

it('serves dashboard counts only for modules the user can view', function () {
    $this->seed(DashboardModuleSeeder::class);
    Faq::create(['question' => 'Q', 'answer' => 'A']);
    Faq::create(['question' => 'Q2', 'answer' => 'A']);

    $this->getJson('/api/v1/dashboard')->assertUnauthorized();

    actingAsRole('nobody');
    $this->getJson('/api/v1/dashboard')->assertForbidden();

    actingAsRole('viewer', ['dashboard.view', 'faqs.view']);
    $response = $this->getJson('/api/v1/dashboard')->assertOk()->assertJsonPath('data.faqs', 2);

    expect(array_keys($response->json('data')))->toBe(['faqs']);
});

it('adds unread contact messages for users who can view the inbox', function () {
    ContactMessage::create(['name' => 'A', 'email' => 'a@example.com', 'message' => 'm']);
    ContactMessage::create(['name' => 'B', 'email' => 'b@example.com', 'message' => 'm', 'read_at' => now()]);
    actingAsRole('viewer', ['dashboard.view', 'contact.view']);

    $this->getJson('/api/v1/dashboard')->assertOk()->assertJsonPath('data.unread_messages', 1);
});
