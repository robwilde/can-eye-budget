<?php

namespace App\Data;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

class ProjectionData extends Data
{
    public function __construct(
        #[WithCast(DateTimeInterfaceCast::class)]
        public Carbon $date,
        
        public float $balance,
        public float $income,
        public float $expenses,
        public float $net,
        public bool $is_negative,
        public Collection $transactions,
    ) {}
}