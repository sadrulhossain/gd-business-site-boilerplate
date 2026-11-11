<?php

use App\Models\Section;
use App\Support\Bilingual;

it('derives the second locale from config', function () {
    expect(config('locales.second'))->toBeNull(); // LOCALE_SUPPORTED=en
});

it('picks the *_2nd column in the second locale and falls back when empty', function () {
    config(['locales.default' => 'en', 'locales.second' => 'bn']);
    $section = new Section(['title' => 'About', 'title_2nd' => 'সম্পর্কে', 'content' => 'Text']);

    app()->setLocale('en');
    expect($section->localized('title'))->toBe('About');

    app()->setLocale('bn');
    expect($section->localized('title'))->toBe('সম্পর্কে')
        ->and($section->localized('content'))->toBe('Text'); // no content_2nd: first language
});

it('adds nullable *_2nd rules next to each field', function () {
    expect(Bilingual::rules(['title' => ['sometimes', 'required', 'string', 'max:150']]))->toBe([
        'title' => ['sometimes', 'required', 'string', 'max:150'],
        'title_2nd' => ['nullable', 'string', 'max:150'],
    ]);
});
