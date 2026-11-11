<?php

it('lists locales from config', function () {
    config([
        'locales.default' => 'en',
        'locales.fallback' => 'en',
        'locales.supported' => ['en', 'bn'],
    ]);

    $this->getJson('/api/v1/meta/locales')
        ->assertOk()
        ->assertExactJson([
            'data' => ['default' => 'en', 'fallback' => 'en', 'supported' => ['en', 'bn']],
        ]);
});

it('parses LOCALE_SUPPORTED as a trimmed comma list', function () {
    putenv('LOCALE_SUPPORTED= en, bn ,');
    $_ENV['LOCALE_SUPPORTED'] = $_SERVER['LOCALE_SUPPORTED'] = ' en, bn ,';

    expect((require base_path('config/locales.php'))['supported'])->toBe(['en', 'bn']);

    putenv('LOCALE_SUPPORTED');
    unset($_ENV['LOCALE_SUPPORTED'], $_SERVER['LOCALE_SUPPORTED']);
});
