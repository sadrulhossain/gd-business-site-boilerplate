<?php

// Container env (compose) beats phpunit.xml <env>, because Laravel reads $_SERVER first.
// So force the test environment here, before the app boots:
// - the database is "<dev database>_testing" (tests refresh it, never the dev one)
// - cache/session/queue/log must not touch the real cache tables or logs.
require __DIR__.'/../vendor/autoload.php';

$database = getenv('DB_DATABASE') ?: 'app';

if (! str_ends_with($database, '_testing')) {
    $database .= '_testing';
}

$overrides = [
    'APP_ENV' => 'testing',
    'DB_DATABASE' => $database,
    'CACHE_STORE' => 'array',
    'SESSION_DRIVER' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'LOG_CHANNEL' => 'null',
    'LOCALE_SUPPORTED' => 'en', // tests assume one locale unless they set config themselves; the dev .env may enable a second
];

foreach ($overrides as $name => $value) {
    putenv("{$name}={$value}");
    $_ENV[$name] = $_SERVER[$name] = $value;
}
