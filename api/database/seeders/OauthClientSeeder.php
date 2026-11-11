<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\Passport;

/** First-party admin client: public, Authorization Code + PKCE, fixed id from OAUTH_ADMIN_CLIENT_ID. */
class OauthClientSeeder extends Seeder
{
    public function run(): void
    {
        $id = config('access.oauth_admin_client.id');
        $redirect = config('access.oauth_admin_client.redirect_uri');

        if (! $id || ! $redirect) {
            $this->command?->warn('OauthClientSeeder skipped: set OAUTH_ADMIN_CLIENT_ID and OAUTH_ADMIN_REDIRECT_URI in api/.env');

            return;
        }

        $client = Passport::client()->newQuery()->find($id) ?? Passport::client()->forceFill(['id' => $id]);

        $client->forceFill([
            'name' => 'Admin',
            'secret' => null, // public client: PKCE instead of a secret
            'redirect_uris' => [$redirect],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'revoked' => false,
        ])->save();
    }
}
