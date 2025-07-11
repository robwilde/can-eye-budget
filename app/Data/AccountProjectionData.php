<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class AccountProjectionData extends Data
{
    public function __construct(
        public string $name,
        public float $balance,
        public float $income,
        public float $expenses,
    ) {}
}