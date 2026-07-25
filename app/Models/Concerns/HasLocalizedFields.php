<?php

namespace App\Models\Concerns;

trait HasLocalizedFields
{
    /**
     * Resolve a localized JSON field to the current locale, falling back to English.
     *
     * @param  array<string, string>|null  $field
     */
    public function localized(?array $field): string
    {
        if ($field === null) {
            return '';
        }

        return $field[app()->getLocale()] ?? $field['en'] ?? '';
    }
}
