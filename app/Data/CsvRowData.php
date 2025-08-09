<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Category;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class CsvRowData extends Data
{
    public function __construct(
        public array $raw_data,
        public string $csv_row_hash,
        public string $type,
        public float $amount,
        public string $description,

        #[WithCast(DateTimeInterfaceCast::class)]
        public Carbon $date,

        public Optional|float $debit,
        public Optional|float $credit,
        public Optional|float $balance,
        public Optional|Category|null $suggested_category,
        public Optional|int|null $category_id,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            raw_data: $data['raw_data'] ?? [],
            csv_row_hash: $data['csv_row_hash'] ?? '',
            type: $data['type'] ?? 'expense',
            amount: (float) ($data['amount'] ?? 0),
            description: $data['description'] ?? '',
            date: $data['date'] ?? now(),
            debit: isset($data['debit']) ? (float) $data['debit'] : Optional::create(),
            credit: isset($data['credit']) ? (float) $data['credit'] : Optional::create(),
            balance: isset($data['balance']) ? (float) $data['balance'] : Optional::create(),
            suggested_category: $data['suggested_category'] ?? Optional::create(),
            category_id: $data['category_id'] ?? Optional::create(),
        );
    }
}
