<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public contact form
    |--------------------------------------------------------------------------
    |
    | captcha_ttl: seconds a captcha stays valid (it is also consumed on first use).
    | per_minute: submissions per IP per minute (named limiter "contact").
    | notify_to: fallback recipient when Settings has no email.
    |
    */

    'captcha_ttl' => 600,

    'per_minute' => 5,

    'notify_to' => env('CONTACT_NOTIFY_TO'),

];
