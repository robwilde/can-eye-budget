<?php

declare(strict_types=1);

namespace App\Services\DateParsing\Parsers;

use App\Services\DateParsing\DateParserInterface;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Exception;

abstract class AbstractDateParser implements DateParserInterface
{
    final public function parse(string $value): ?CarbonInterface
    {
        // Handle empty dates
        if (empty(mb_trim($value))) {
            return null;
        }

        $value = mb_trim($value);

        // Try locale-specific formats first
        foreach ($this->getFormats() as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $value);

                // Validate that the parsing was successful and makes sense
                if ($parsed && $this->isValidParsedDate($parsed, $value, $format)) {
                    return $parsed;
                }
            } catch (Exception $e) {
                continue;
            }
        }

        // Try Carbon's flexible parser as fallback (but with caution)
        try {
            $parsed = Carbon::parse($value);

            // Only accept if it's a reasonable date (not too far in past/future)
            if ($this->isReasonableDate($parsed)) {
                return $parsed;
            }
        } catch (Exception $e) {
            // Fall through to return null
        }

        return null;
    }

    /**
     * Validate that a parsed date makes sense given the original input.
     */
    protected function isValidParsedDate(CarbonInterface $parsed, string $original, string $format): bool
    {
        // Check that the parsed date formats back to the original
        // This catches auto-corrected invalid dates like 32/03/2025 -> 01/04/2025
        $reformatted = $parsed->format($format);

        // Normalize for comparison (handle leading zeros)
        $normalizedOriginal = $this->normalizeDate($original, $format);
        $normalizedReformatted = $this->normalizeDate($reformatted, $format);

        return $normalizedOriginal === $normalizedReformatted;
    }

    /**
     * Check if a date is within reasonable bounds (not too far past/future).
     */
    protected function isReasonableDate(CarbonInterface $date): bool
    {
        $now = Carbon::now();
        $twoYearsAgo = $now->copy()->subYears(2);
        $twoYearsFromNow = $now->copy()->addYears(2);

        return $date->between($twoYearsAgo, $twoYearsFromNow);
    }

    /**
     * Normalize a date string for comparison.
     */
    private function normalizeDate(string $date, string $format): string
    {
        // For formats with single digits, ensure consistent formatting
        if (str_contains($format, 'j') || str_contains($format, 'n')) {
            // Convert to consistent format for comparison
            $parts = preg_split('/[\/\-]/', $date);
            if (count($parts) === 3) {
                // Remove leading zeros for consistent comparison
                $parts = array_map(static fn ($part) => mb_ltrim($part, '0') ?: '0', $parts);

                return implode('/', $parts);
            }
        }

        return $date;
    }
}
