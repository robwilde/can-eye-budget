<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

final class DuplicateData extends Data
{
    public function __construct(
        public CsvRowData $csv_row,
        public string $csv_row_hash,
        public Collection $existing_transactions,
        public float $confidence,
    ) {}
}
