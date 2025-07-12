<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

final class ImportResultData extends Data
{
    public function __construct(
        public bool $success,
        public ImportData $import,
        public int $created_count,
        public int $duplicate_count,
        public int $total_rows,
        public Collection $duplicates,
        public Collection $created_transactions,
        public ?string $error = null,
    ) {}
}
