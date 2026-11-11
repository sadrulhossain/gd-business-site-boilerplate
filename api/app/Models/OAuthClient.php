<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Passport\Client;
use Laravel\Passport\Scope;

/**
 * Passport client that skips the consent screen for the first-party admin client.
 */
class OAuthClient extends Client
{
    /** @param  Scope[]  $scopes */
    public function skipsAuthorization(Authenticatable $user, array $scopes): bool
    {
        return $this->getKey() === config('access.oauth_admin_client.id');
    }
}
