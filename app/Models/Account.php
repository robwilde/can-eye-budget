<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_category_id',
        'name',
        'type',
        'initial_balance',
        'credit_limit',
        'currency',
        'description',
        'is_visible_in_totals',
    ];

    protected $casts = [
        'initial_balance'      => 'decimal:2',
        'credit_limit'         => 'decimal:2',
        'is_visible_in_totals' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accountCategory(): BelongsTo
    {
        return $this->belongsTo(AccountCategory::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function recurringPatterns(): HasMany
    {
        return $this->hasMany(RecurringPattern::class);
    }

    public function transfersIn(): HasMany
    {
        return $this->hasMany(Transaction::class, 'transfer_to_account_id');
    }

    public function getCurrentBalance(): float
    {
        $transactionSum = $this->transactions()
            ->entered() // Only include entered transactions, not planned
            ->selectRaw('
                SUM(CASE 
                    WHEN type = "income" THEN amount 
                    WHEN type = "expense" THEN -amount 
                    WHEN type = "transfer" THEN -amount 
                    ELSE 0 
                END) as balance
            ')
            ->value('balance') ?? 0;

        $transfersInSum = $this->transfersIn()
            ->where('type', 'transfer')
            ->where('status', 'entered') // Only include entered transfers, not planned
            ->sum('amount') ?? 0;

        return (float) ($this->initial_balance + $transactionSum + $transfersInSum);
    }

    public function scopeVisibleInTotals($query)
    {
        return $query->where('is_visible_in_totals', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function isCreditAccount(): bool
    {
        return $this->type === 'credit';
    }

    public function getAvailableCredit(): ?float
    {
        if (! $this->isCreditAccount() || ! $this->credit_limit) {
            return null;
        }

        return (float) ($this->credit_limit + $this->getCurrentBalance());
    }
}
