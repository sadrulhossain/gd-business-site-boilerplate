<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

/** @return array<string, string> label => uri, for every route behind auth:api */
function guardedRoutes(): array
{
    $routes = [];
    foreach (Route::getRoutes()->getRoutes() as $route) {
        if (str_starts_with($route->uri(), 'api/') && in_array('auth:api', $route->gatherMiddleware(), true)) {
            $routes[$route->methods()[0].' '.$route->uri()] = $route->uri();
        }
    }

    return $routes;
}

it('has a permission or policy check on every authenticated route (php artisan security:routes)', function () {
    $this->artisan('security:routes')->assertSuccessful();
});

it('answers 401 to anonymous callers on every authenticated route, before any model lookup', function () {
    foreach (guardedRoutes() as $label => $uri) {
        [$method] = explode(' ', $label);
        $path = '/'.preg_replace('/\{[^}]+\}/', '999999', $uri);

        $this->json($method, $path)->assertUnauthorized();
    }
});

it('answers 403 to a signed-in user without permissions on every collection route', function () {
    actingAsRole('nobody');
    // Own-account routes are open to every signed-in user by design.
    $open = ['GET api/v1/me', 'PATCH api/v1/me', 'GET api/v1/me/menus', 'POST api/v1/logout'];
    $checked = 0;

    foreach (guardedRoutes() as $label => $uri) {
        if (in_array($label, $open, true) || str_contains($uri, '{')) {
            continue; // routes with an id are covered by their module tests (binding runs before the policy)
        }
        [$method] = explode(' ', $label);

        $this->json($method, '/'.$uri)->assertForbidden();
        $checked++;
    }

    expect($checked)->toBeGreaterThan(15);
});

it('keeps CORS closed: no Access-Control-Allow-Origin for a foreign origin, even on preflight', function () {
    $headers = ['Origin' => 'https://evil.example', 'Access-Control-Request-Method' => 'GET'];

    $this->get('/api/v1/public/settings', ['Origin' => 'https://evil.example'])->assertHeaderMissing('Access-Control-Allow-Origin');
    $this->call('OPTIONS', '/api/v1/me', [], [], [], collect($headers)->mapWithKeys(fn ($v, $k) => ['HTTP_'.strtoupper(str_replace('-', '_', $k)) => $v])->all())
        ->assertHeaderMissing('Access-Control-Allow-Origin');
});

it('allows only the configured origins and never credentials', function () {
    config(['cors.allowed_origins' => ['https://admin.example.test']]);

    $this->get('/api/v1/public/settings', ['Origin' => 'https://admin.example.test'])
        ->assertHeader('Access-Control-Allow-Origin', 'https://admin.example.test')
        ->assertHeaderMissing('Access-Control-Allow-Credentials');
    expect($this->get('/api/v1/public/settings', ['Origin' => 'https://evil.example'])->headers->get('Access-Control-Allow-Origin'))
        ->not->toBe('https://evil.example')->not->toBe('*');
});

it('does not leak internals in error responses', function () {
    $this->getJson('/api/v1/does-not-exist')->assertNotFound()->assertJsonMissingPath('trace')->assertJsonMissingPath('file');

    actingAsRole('nobody');
    $response = $this->getJson('/api/v1/users')->assertForbidden();
    expect($response->getContent())->not->toContain('vendor/')->not->toContain('Illuminate');
});
