<?php

/*
| CORS is closed by default. The admin SPA is served same-origin with the API (nginx proxies /api and /oauth/token), and the
| portal calls the API server-side, so no browser needs cross-origin access. Without this file Laravel answers
| `Access-Control-Allow-Origin: *`. Set CORS_ALLOWED_ORIGINS (comma separated, exact origins) only when a browser app
| on another origin must call the API. Credentials are never allowed: the API uses bearer tokens.
*/
return [

    'paths' => ['api/*', 'oauth/token'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Authorization', 'Content-Type', 'Accept', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 600,

    'supports_credentials' => false,

];
