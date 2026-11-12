<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Portal revalidation webhook
    |--------------------------------------------------------------------------
    |
    | After public content changes, a queued job POSTs the changed tags to this
    | URL, signed with HMAC-SHA256 of "<timestamp>.<body>" using the secret
    | (headers X-Webhook-Timestamp, X-Webhook-Signature: sha256=<hex>).
    | An empty URL disables the webhook.
    |
    */

    'revalidate_url' => env('PORTAL_REVALIDATE_URL'),

    'revalidate_secret' => env('PORTAL_REVALIDATE_SECRET'),

];
