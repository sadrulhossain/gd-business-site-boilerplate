<?php

use App\Models\User;
use Database\Seeders\OauthClientSeeder;
use Illuminate\Support\Str;

const PKCE_CLIENT = 'e98a6a07-2975-4f52-941e-d9b6b27eb580';
const PKCE_REDIRECT = 'http://admin.example.test/auth/callback';

function pkceChallenge(string $verifier): string
{
    return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
}

function authorizeUrl(string $verifier, string $state = 'abc'): string
{
    return '/oauth/authorize?'.http_build_query([
        'client_id' => PKCE_CLIENT,
        'redirect_uri' => PKCE_REDIRECT,
        'response_type' => 'code',
        'code_challenge' => pkceChallenge($verifier),
        'code_challenge_method' => 'S256',
        'state' => $state,
    ]);
}

beforeEach(function () {
    config(['access.oauth_admin_client' => ['id' => PKCE_CLIENT, 'redirect_uri' => PKCE_REDIRECT]]);
    $this->seed(OauthClientSeeder::class);
    $this->verifier = Str::random(64);
});

it('sends a guest from /oauth/authorize to the login page, not a JSON 401', function () {
    $this->get(authorizeUrl($this->verifier))->assertRedirect('/login');
});

it('completes the whole PKCE flow: login, code, token, API call, refresh', function () {
    $user = User::factory()->create(['password' => 'secret-pass']);
    $authorize = authorizeUrl($this->verifier);

    $this->get($authorize)->assertRedirect('/login');
    // Back to the authorize URL (Laravel may reorder its query string).
    expect($this->post('/login', ['email' => $user->email, 'password' => 'secret-pass'])->assertRedirect()->headers->get('Location'))
        ->toStartWith(url('/oauth/authorize?'));

    $redirect = $this->get($authorize)->assertRedirect()->headers->get('Location');
    expect($redirect)->toStartWith(PKCE_REDIRECT.'?');
    parse_str(parse_url($redirect, PHP_URL_QUERY), $query);
    expect($query['state'])->toBe('abc')->and($query['code'])->not->toBeEmpty();

    $token = $this->postJson('/oauth/token', [
        'grant_type' => 'authorization_code',
        'client_id' => PKCE_CLIENT,
        'redirect_uri' => PKCE_REDIRECT,
        'code' => $query['code'],
        'code_verifier' => $this->verifier,
    ])->assertOk()->json();

    expect($token)->toHaveKeys(['access_token', 'refresh_token', 'expires_in'])->and($token['expires_in'])->toBeGreaterThan(3000);

    $this->withToken($token['access_token'])->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.email', $user->email);

    $refreshed = $this->postJson('/oauth/token', [
        'grant_type' => 'refresh_token',
        'client_id' => PKCE_CLIENT,
        'refresh_token' => $token['refresh_token'],
    ])->assertOk()->json();

    expect($refreshed['access_token'])->not->toBe($token['access_token']);

    // Sign out revokes the token it was called with (guards are cached per app instance, so reset them between tokens).
    $this->app['auth']->forgetGuards();
    $this->withToken($refreshed['access_token'])->postJson('/api/v1/logout')->assertNoContent();
    $this->app['auth']->forgetGuards();
    $this->withToken($refreshed['access_token'])->getJson('/api/v1/me')->assertUnauthorized();
});

it('rejects a code exchanged with the wrong verifier', function () {
    $user = User::factory()->create(['password' => 'secret-pass']);
    $authorize = authorizeUrl($this->verifier);

    $this->get($authorize);
    $this->post('/login', ['email' => $user->email, 'password' => 'secret-pass']);
    $redirect = $this->get($authorize)->headers->get('Location');
    parse_str(parse_url($redirect, PHP_URL_QUERY), $query);

    $this->postJson('/oauth/token', [
        'grant_type' => 'authorization_code',
        'client_id' => PKCE_CLIENT,
        'redirect_uri' => PKCE_REDIRECT,
        'code' => $query['code'],
        'code_verifier' => Str::random(64),
    ])->assertStatus(400);
});

it('refuses clients without a secret to use the password grant', function () {
    $this->postJson('/oauth/token', [
        'grant_type' => 'password',
        'client_id' => PKCE_CLIENT,
        'username' => 'a@example.com',
        'password' => 'x',
    ])->assertStatus(400);
});
