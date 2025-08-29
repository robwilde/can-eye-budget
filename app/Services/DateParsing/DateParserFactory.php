<?php

declare(strict_types=1);

namespace App\Services\DateParsing;

use App\Services\DateParsing\Parsers\AustralianDateParser;

final class DateParserFactory
{
    /**
     * Create a date parser instance based on the application locale.
     */
    public function make(?string $locale = null): DateParserInterface
    {
        $locale = $locale ?: (string) config('app.locale', 'en_AU');

        return match ($locale) {
            'en_AU' => new AustralianDateParser(),

            // Future locale support can be added here:
            // 'en_US' => new AmericanDateParser(),
            // 'en_GB' => new BritishDateParser(),
            // 'de_DE' => new GermanDateParser(),

            // Default to Australian parser for now
            default => new AustralianDateParser(),
        };
    }

    /**
     * Create a parser for a specific locale (useful for testing).
     */
    public function makeForLocale(string $locale): DateParserInterface
    {
        return $this->make($locale);
    }

    /**
     * Get all supported locales.
     */
    public function getSupportedLocales(): array
    {
        return [
            'en_AU',
            // Future locales will be listed here
        ];
    }

    /**
     * Check if a locale is supported.
     */
    public function isLocaleSupported(string $locale): bool
    {
        return in_array($locale, $this->getSupportedLocales(), true) || $locale === config('app.locale');
    }
}
