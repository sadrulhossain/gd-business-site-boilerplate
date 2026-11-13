<?php

use App\Models\Section;
use App\Models\User;
use Database\Seeders\OauthClientSeeder;
use Database\Seeders\SectionsModuleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

const HARD_CLIENT = 'e98a6a07-2975-4f52-941e-d9b6b27eb580';
const HARD_REDIRECT = 'http://admin.example.test/auth/callback';

function hardAuthorize(array $override = []): array
{
    $verifier = Str::random(64);

    return [$verifier, '/oauth/authorize?'.http_build_query($override + [
        'client_id' => HARD_CLIENT,
        'redirect_uri' => HARD_REDIRECT,
        'response_type' => 'code',
        'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
        'code_challenge_method' => 'S256',
        'state' => 'abc',
    ])];
}

describe('OAuth authorization (PKCE)', function () {
    beforeEach(function () {
        config(['access.oauth_admin_client' => ['id' => HARD_CLIENT, 'redirect_uri' => HARD_REDIRECT]]);
        $this->seed(OauthClientSeeder::class);
        $this->actingAs(User::factory()->create(), 'web');
    });

    it('does not issue a code without a code_challenge', function () {
        [, $url] = hardAuthorize(['code_challenge' => null, 'code_challenge_method' => null]);

        $response = $this->get($url);
        expect((string) $response->headers->get('Location'))->not->toContain('code=');
        expect($response->getStatusCode())->toBe(400);
    });

    it('does not issue a code for the plain challenge method', function () {
        [, $url] = hardAuthorize(['code_challenge_method' => 'plain']);

        $response = $this->get($url);
        expect($response->getStatusCode())->toBe(400)->and((string) $response->headers->get('Location'))->not->toContain('code=');
    });

    it('never redirects to an unregistered redirect_uri', function () {
        [, $url] = hardAuthorize(['redirect_uri' => 'https://evil.example/cb']);

        expect((string) $this->get($url)->headers->get('Location'))->not->toContain('evil.example');
    });

    it('refuses to redeem an authorization code twice', function () {
        [$verifier, $url] = hardAuthorize();
        parse_str(parse_url($this->get($url)->assertRedirect()->headers->get('Location'), PHP_URL_QUERY), $query);
        $body = ['grant_type' => 'authorization_code', 'client_id' => HARD_CLIENT, 'redirect_uri' => HARD_REDIRECT, 'code' => $query['code'], 'code_verifier' => $verifier];

        $this->postJson('/oauth/token', $body)->assertOk();
        $this->postJson('/oauth/token', $body)->assertStatus(400);
    });

    it('does not offer client_credentials or implicit flows to the admin client', function () {
        $this->postJson('/oauth/token', ['grant_type' => 'client_credentials', 'client_id' => HARD_CLIENT])->assertStatus(400);
        [, $url] = hardAuthorize(['response_type' => 'token']);

        expect((string) $this->get($url)->headers->get('Location'))->not->toContain('access_token');
    });
});

describe('uploads', function () {
    beforeEach(function () {
        Storage::fake('uploads');
        $this->seed(SectionsModuleSeeder::class);
        $this->section = Section::where('key', 'hero')->firstOrFail();
        actingAsRole('editor', ['sections.update']);
    });

    $upload = fn ($test, UploadedFile $file) => $test->post("/api/v1/sections/{$test->section->id}/image", ['image' => $file], ['Accept' => 'application/json']);

    it('rejects PHP, HTML and SVG payloads dressed up as images', function () use ($upload) {
        foreach (['shell.php.jpg' => '<?php system($_GET["c"]);', 'page.png' => '<html><script>alert(1)</script></html>', 'logo.svg' => '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'] as $name => $content) {
            $upload($this, UploadedFile::fake()->createWithContent($name, $content))->assertStatus(422);
        }

        expect($this->section->refresh()->image)->toBeNull();
    });

    it('stores a genuine image under a generated name, never the client one', function () use ($upload) {
        $upload($this, UploadedFile::fake()->image('../../evil.php.jpg', 100, 100))->assertOk();

        $path = $this->section->refresh()->image;
        expect($path)->toMatch('#^sections/[a-z0-9]{32}/original\.jpg$#')->not->toContain('evil')->not->toContain('..');
        expect(collect(Storage::disk('uploads')->allFiles())->filter(fn ($f) => preg_match('/\.(php|phtml|phar|html?|svg)$/i', $f)))->toBeEmpty();
    });
});

it('rate limits the captcha endpoint', function () {
    foreach (range(1, 30) as $ignored) {
        $this->getJson('/api/v1/public/captcha')->assertOk();
    }

    $this->getJson('/api/v1/public/captcha')->assertStatus(429);
});
