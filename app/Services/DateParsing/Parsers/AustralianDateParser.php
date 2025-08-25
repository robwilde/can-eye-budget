<?php

declare(strict_types=1);

namespace App\Services\DateParsing\Parsers;

final class AustralianDateParser extends AbstractDateParser
{
    public function getLocale(): string
    {
        return 'en_AU';
    }

    public function getFormats(): array
    {
        return [
            // Australian date formats in priority order
            'd/m/Y',        // 01/03/2025 = March 1st (most common bank statement format)
            'j/n/Y',        // 1/3/2025 = March 1st (single digits)
            'd-m-Y',        // 01-03-2025 = March 1st (hyphens)
            'j-n-Y',        // 1-3-2025 = March 1st (single digits with hyphens)
            'd/m/Y H:i:s',  // 01/03/2025 14:30:25 (with time)
            'j/n/Y H:i:s',  // 1/3/2025 14:30:25 (single digits with time)
            'd-m-Y H:i:s',  // 01-03-2025 14:30:25 (hyphens with time)

            // ISO format as fallback (international standard)
            'Y-m-d',        // 2025-03-01 (ISO format)
            'Y-m-d H:i:s',  // 2025-03-01 14:30:25 (ISO with time)
        ];
    }
}
