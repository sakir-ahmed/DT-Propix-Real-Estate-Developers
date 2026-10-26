<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Support\Website\Localizer;
use Illuminate\Database\Eloquent\Model;

/**
 * Bilingual content helpers. JSON columns hold {"en": "...", "bn": "..."};
 * older models keep paired `*_en` / `*_bn` columns. Both fall back to the
 * application's fallback locale when a translation is missing.
 *
 * @mixin Model
 */
trait HasTranslations
{
    public function translate(string $attribute, ?string $locale = null): ?string
    {
        $value = $this->getAttribute($attribute);

        if (is_string($value)) {
            return $value;
        }

        if (! is_array($value)) {
            return null;
        }

        return Localizer::pick($value, $locale);
    }

    public function translateColumn(string $base, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $value = $this->getAttribute($base.'_'.$locale);

        if (is_string($value) && $value !== '') {
            return $value;
        }

        $fallback = $this->getAttribute($base.'_'.Localizer::fallbackLocale());

        return is_string($fallback) && $fallback !== '' ? $fallback : null;
    }

    /**
     * @return list<array<string, mixed>|string>
     */
    public function translateList(string $attribute, ?string $locale = null): array
    {
        $items = $this->getAttribute($attribute);

        return is_array($items) ? Localizer::list($items, $locale) : [];
    }
}
