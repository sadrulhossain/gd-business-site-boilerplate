<?php

// Tests refresh their database, so never let them touch the dev one:
// force DB_DATABASE to "<dev database>_testing" before the app boots.
require __DIR__.'/../vendor/autoload.php';

$database = getenv('DB_DATABASE') ?: 'app';

if (! str_ends_with($database, '_testing')) {
    $database .= '_testing';
}

putenv("DB_DATABASE={$database}");
$_ENV['DB_DATABASE'] = $_SERVER['DB_DATABASE'] = $database;
