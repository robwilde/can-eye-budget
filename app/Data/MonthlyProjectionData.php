<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class MonthlyProjectionData extends Data
{
    public function __construct(
        public string $month,
        public string $month_name,
        public array $accounts,
        public float $total_balance,
        public float $total_income,
        public float $total_expenses,
    ) {}
}
