<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id',
        'type',
        'amount',
        'description',
        'transaction_date',
        'category_id',
        'transfer_to_account_id',
        'transfer_pair_id',
        'recurring_pattern_id',
        'import_id',
        'reconciled',
        'status',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'transaction_date' => 'date',
        'reconciled'       => 'boolean',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transferToAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'transfer_to_account_id');
    }

    public function recurringPattern(): BelongsTo
    {
        return $this->belongsTo(RecurringPattern::class);
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeTransfer($query)
    {
        return $query->where('type', 'transfer');
    }

    public function scopeReconciled($query)
    {
        return $query->where('reconciled', true);
    }

    public function scopeUnreconciled($query)
    {
        return $query->where('reconciled', false);
    }

    public function scopePlanned($query)
    {
        return $query->where('status', 'planned');
    }

    public function scopeEntered($query)
    {
        return $query->where('status', 'entered');
    }

    public function scopeForDateRange($query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopeForMonth($query, $year, $month): Builder
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return $query->forDateRange($startDate, $endDate);
    }

    public function getSignedAmountAttribute(): float
    {
        return match ($this->type) {
            'income' => (float) $this->amount,
            'expense', 'transfer' => -(float) $this->amount,
            default => 0
        };
    }

    public function isTransfer(): bool
    {
        return $this->type === 'transfer';
    }

    public function isRecurring(): bool
    {
        return ! is_null($this->recurring_pattern_id);
    }

    public function isPlanned(): bool
    {
        return $this->status === 'planned';
    }

    public function isEntered(): bool
    {
        return $this->status === 'entered';
    }

    public function transferPairTransaction(): ?self
    {
        if (! $this->transfer_pair_id) {
            return null;
        }

        return self::where('transfer_pair_id', $this->transfer_pair_id)
                   ->where('id', '!=', $this->id)
                   ->first();
    }

    public function isTransferSource(): bool
    {
        return $this->isTransfer() && ! is_null($this->transfer_to_account_id);
    }

    public function isTransferDestination(): bool
    {
        return $this->isTransfer() && is_null($this->transfer_to_account_id) && ! is_null($this->transfer_pair_id);
    }

    public function getTransferDisplayType(): string
    {
        if (! $this->isTransfer()) {
            return $this->type;
        }

        return $this->isTransferSource() ? 'transfer_out' : 'transfer_in';
    }

    public function scopeUniqueDescriptions(Builder $query, ?int $accountId = null): Builder
    {
        $query = $query->select('description')
                       ->selectRaw('COUNT(*) as usage_count')
                       ->whereNotNull('description')
                       ->where('description', '!=', '')
                       ->whereRaw('TRIM(description) != ""')
                       ->groupBy('description')
                       ->orderByDesc('usage_count')
                       ->orderBy('description');

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        return $query;
    }

    public function scopeDescriptionSearch(Builder $query, string $term, ?int $accountId = null): Builder
    {
        $term = mb_trim($term);

        if (empty($term)) {
            return $query->whereRaw('1 = 0'); // Return empty result for empty search
        }

        $query = $query->select('description')
                       ->selectRaw('COUNT(*) as usage_count')
                       ->selectRaw('CASE
                           WHEN LOWER(description) = LOWER(?) THEN 4
                           WHEN LOWER(description) LIKE LOWER(?) THEN 3
                           WHEN LOWER(description) LIKE LOWER(?) THEN 2
                           ELSE 1
                       END as relevance_score', [
                           $term,
                           $term.'%',
                           '%'.$term.'%',
                       ])
                       ->whereNotNull('description')
                       ->where('description', '!=', '')
                       ->whereRaw('TRIM(description) != ""')
                       ->where(function ($q) use ($term) {
                           $q->where('description', 'LIKE', '%'.$term.'%');
                       })
                       ->groupBy('description')
                       ->orderByDesc('relevance_score')
                       ->orderByDesc('usage_count')
                       ->orderBy('description')
                       ->limit(10);

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        return $query;
    }
}
