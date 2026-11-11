<?php

$supported = array_values(array_filter(array_map('trim', explode(',', (string) env('LOCALE_SUPPORTED', 'en')))));
$default = env('LOCALE_DEFAULT', 'en');

return [

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    | LOCALE_SUPPORTED is a comma separated list, e.g. "en,bn". "second" is the
    | first supported locale that is not the default: the language stored in
    | the *_2nd columns. Null when only one locale is supported.
    |
    */

    'default' => $default,

    'fallback' => env('LOCALE_FALLBACK', 'en'),

    'supported' => $supported,

    'second' => collect($supported)->first(fn (string $locale) => $locale !== $default),

];
