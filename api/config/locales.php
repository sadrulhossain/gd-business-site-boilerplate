<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    | LOCALE_SUPPORTED is a comma separated list, e.g. "en,bn".
    |
    */

    'default' => env('LOCALE_DEFAULT', 'en'),

    'fallback' => env('LOCALE_FALLBACK', 'en'),

    'supported' => array_values(array_filter(array_map('trim', explode(',', (string) env('LOCALE_SUPPORTED', 'en'))))),

];
