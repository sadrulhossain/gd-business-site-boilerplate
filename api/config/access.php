<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Superadmin
    |--------------------------------------------------------------------------
    |
    | The role that passes every Gate check, and the account SuperadminSeeder
    | creates from api/.env. The seeder skips when email/password are empty.
    |
    */

    'superadmin_role' => 'superadmin',

    'superadmin' => [
        'name' => env('SUPERADMIN_NAME', 'Super Admin'),
        'email' => env('SUPERADMIN_EMAIL'),
        'password' => env('SUPERADMIN_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | First-party admin OAuth client
    |--------------------------------------------------------------------------
    |
    | Public (no secret) Authorization Code + PKCE client with a fixed id, so
    | the admin app can be configured before the database is seeded.
    |
    */

    'oauth_admin_client' => [
        'id' => env('OAUTH_ADMIN_CLIENT_ID'),
        'redirect_uri' => env('OAUTH_ADMIN_REDIRECT_URI'),
    ],

];
