<?php

namespace App\Support;

/** Validation helper for bilingual fields. */
final class Bilingual
{
    /**
     * Adds a nullable `<field>_2nd` rule set next to every field.
     *
     * @param  array<string, list<string>>  $rules
     * @return array<string, list<string>>
     */
    public static function rules(array $rules): array
    {
        $all = $rules;

        foreach ($rules as $field => $fieldRules) {
            $all[$field.'_2nd'] = ['nullable', ...array_values(array_diff($fieldRules, ['required', 'sometimes', 'nullable']))];
        }

        return $all;
    }
}
