<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace App\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

final class TransactionChangeData extends Data
{
    public function __construct(
        public int $transaction_id,
        public string $description,
        public float $amount,
        #[WithCast(DateTimeInterfaceCast::class)]
        public Carbon $date,
        public ?string $current_category,
        public ?string $proposed_category,
        public string $account_name,
        public float $confidence = 1.0,
        public ?string $match_reason = null,
    ) {}

    public function isRecategorization(): bool
    {
        return $this->current_category !== null &&
               $this->current_category !== $this->proposed_category;
    }

    public function isNewCategorization(): bool
    {
        return $this->current_category === null &&
               $this->proposed_category !== null;
    }

    public function getChangeType(): string
    {
        if ($this->isNewCategorization()) {
            return 'new';
        }

        if ($this->isRecategorization()) {
            return 'change';
        }

        return 'none';
    }

    public function getFormattedAmount(): string
    {
        return '$'.number_format(abs($this->amount), 2);
    }

    public function getFormattedDate(): string
    {
        return $this->date->format('M j, Y');
    }
}
