<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Transaction;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

final class DescriptionGroupData extends Data
{
    public function __construct(
        public string $description,
        public int $count,
        public float $total_amount,
        /** @var Collection<Transaction> */
        public Collection $sample_transactions,
    ) {}
}
