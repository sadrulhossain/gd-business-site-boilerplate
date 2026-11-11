<?php

namespace App\Models\Concerns;

/**
 * Bilingual content: every translatable column `x` has a twin `x_2nd` holding
 * the second language (config locales.second). No language codes in columns.
 */
trait HasSecondLanguage
{
    /** Value of `$field` for the current app locale; falls back to the first language when the twin is empty. */
    public function localized(string $field): ?string
    {
        $second = $this->getAttribute($field.'_2nd');

        if (app()->getLocale() === config('locales.second') && filled($second)) {
            return $second;
        }

        return $this->getAttribute($field);
    }
}
