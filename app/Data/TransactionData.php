<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Account;
use App\Models\Category;
use App\Models\Import;
use App\Models\RecurringPattern;
use App\Models\Transaction;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class TransactionData extends Data
{
    public function __construct(
        public Optional|int $id,

        #[Required]
        public int $account_id,

        #[Required, Rule('in:income,expense,transfer')]
        public string $type,

        #[Required, Rule('numeric|min:0')]
        public float $amount,

        #[Required]
        public string $description,

        #[Required, WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public CarbonInterface $transaction_date,

        public Optional|int|null $category_id,

        #[MapName('transfer_to_account_id')]
        public Optional|int|null $transferToAccountId,

        #[MapName('recurring_pattern_id')]
        public Optional|int|null $recurringPatternId,

        #[MapName('import_id')]
        public Optional|int|null $importId,

        public Optional|bool $reconciled,

        #[Rule('in:entered,planned')]
        public Optional|string $status,

        // Relationships (optional for when we need them)
        public Optional|Account $account,
        public Optional|Category|null $category,
        public Optional|Account|null $transferToAccount,
        public Optional|RecurringPattern|null $recurringPattern,
        public Optional|Import|null $import,

        // Computed properties
        public Optional|float $signed_amount,
        public Optional|bool $is_transfer,
        public Optional|bool $is_recurring,
    ) {
        // Set defaults
        $this->reconciled = $this->reconciled ?? false;
        $this->status = $this->status ?? 'entered';

        // Compute derived values
        $this->signed_amount = match ($this->type) {
            'income' => $this->amount,
            'expense', 'transfer' => -$this->amount,
            default => 0
        };

        $this->is_transfer = $this->type === 'transfer';
        $this->is_recurring = ! is_null($this->recurringPatternId instanceof Optional ? null : $this->recurringPatternId);
    }

    public static function fromModel(Transaction $transaction): self
    {
        return new self(
            id                 : $transaction->id,
            account_id         : $transaction->account_id,
            type               : $transaction->type,
            amount             : (float) $transaction->amount,
            description        : $transaction->description,
            transaction_date   : $transaction->transaction_date,
            category_id        : $transaction->category_id,
            transferToAccountId: $transaction->transfer_to_account_id,
            recurringPatternId : $transaction->recurring_pattern_id,
            importId           : $transaction->import_id,
            reconciled         : $transaction->reconciled,
            status             : $transaction->status ?? 'entered',
            account            : $transaction->relationLoaded('account') ? $transaction->account : Optional::create(),
            category           : $transaction->relationLoaded('category') ? $transaction->category : Optional::create(),
            transferToAccount  : $transaction->relationLoaded('transferToAccount') ? $transaction->transferToAccount : Optional::create(),
            recurringPattern   : $transaction->relationLoaded('recurringPattern') ? $transaction->recurringPattern : Optional::create(),
            import             : $transaction->relationLoaded('import') ? $transaction->import : Optional::create(),

            // Computed properties (let constructor calculate these)
            signed_amount      : Optional::create(),
            is_transfer        : Optional::create(),
            is_recurring       : Optional::create(),
        );
    }

    public function toCreateArray(): array
    {
        return [
            'account_id'             => $this->account_id,
            'type'                   => $this->type,
            'amount'                 => $this->amount,
            'description'            => $this->description,
            'transaction_date'       => $this->transaction_date->toDateString(),
            'category_id'            => $this->category_id instanceof Optional ? null : $this->category_id,
            'transfer_to_account_id' => $this->transferToAccountId instanceof Optional ? null : $this->transferToAccountId,
            'recurring_pattern_id'   => $this->recurringPatternId instanceof Optional ? null : $this->recurringPatternId,
            'import_id'              => $this->importId instanceof Optional ? null : $this->importId,
            'reconciled'             => $this->reconciled instanceof Optional ? false : $this->reconciled,
            'status'                 => $this->status instanceof Optional ? 'entered' : $this->status,
        ];
    }

    public function toUpdateArray(): array
    {
        $data = $this->toCreateArray();
        unset($data['account_id']); // Account cannot be changed on update

        return $data;
    }
}
