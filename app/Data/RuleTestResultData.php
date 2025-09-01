<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace App\Data;

use App\Models\Transaction;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class RuleTestResultData extends Data
{
    public function __construct(
        public int $totalMatches,
        public float $totalAmount,
        public int $uncategorizedMatches,
        public int $alreadyCategorizedMatches,
        #[DataCollectionOf(TransactionChangeData::class)]
        public DataCollection $transactions,
        #[DataCollectionOf(RuleConflictData::class)]
        public DataCollection $conflicts,
        public ?string $error = null,
    ) {}

    public static function fromTransactions(Collection $transactions, array $conflicts = []): self
    {
        $transactionChanges = $transactions->map(
            fn (Transaction $transaction) => TransactionChangeData::from([
                'transaction_id'    => $transaction->id,
                'description'       => $transaction->description,
                'amount'            => $transaction->amount,
                'date'              => $transaction->date,
                'current_category'  => $transaction->category?->name,
                'proposed_category' => null, // Will be set by calling code
                'account_name'      => $transaction->account->name,
                'confidence'        => 1.0,
            ])
        );

        return new self(
            totalMatches: $transactions->count(),
            totalAmount: $transactions->sum('amount'),
            uncategorizedMatches: $transactions->whereNull('category_id')->count(),
            alreadyCategorizedMatches: $transactions->whereNotNull('category_id')->count(),
            transactions: TransactionChangeData::collect($transactionChanges, DataCollection::class),
            conflicts: RuleConflictData::collect(collect($conflicts), DataCollection::class),
        );
    }

    public function hasConflicts(): bool
    {
        return $this->conflicts->count() > 0;
    }

    public function getAffectedTransactionIds(): array
    {
        return $this->transactions->pluck('transaction_id')->toArray();
    }
}
