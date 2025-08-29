<?php

declare(strict_types=1);

namespace App\Services\DateParsing;

use Carbon\CarbonInterface;

interface DateParserInterface
{
    /**
     * Parse a date string according to the locale's expected formats.
     */
    public function parse(string $value): ?CarbonInterface;

    /**
     * Get the date formats supported by this parser in priority order.
     */
    public function getFormats(): array;

    /**
     * Get the locale this parser handles.
     */
    public function getLocale(): string;
}
