<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CategoryRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'account_id',
        'field',
        'operator',
        'value',
        'priority',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Scope to order by priority (lower number = higher priority)
     */
    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderBy('priority');
    }

    /**
     * Scope to filter by account
     */
    public function scopeForAccount(Builder $query, ?int $accountId = null): Builder
    {
        if ($accountId === null) {
            return $query->whereNull('account_id');
        }

        return $query->where(function ($q) use ($accountId) {
            $q
                ->where('account_id', $accountId)
                ->orWhereNull('account_id');
        });
    }

    public function matches(string $description, float $amount): bool
    {
        if ($this->field === 'description') {
            $testValue = mb_trim($description);
            $searchValue = mb_trim($this->value);

            return match ($this->operator) {
                'contains'    => mb_stripos($testValue, $searchValue) !== false,
                'equals'      => mb_strtolower($testValue) === mb_strtolower($searchValue),
                'starts_with' => mb_stripos($testValue, $searchValue) === 0,
                'ends_with'   => mb_strripos($testValue, $searchValue) === mb_strlen($testValue) - mb_strlen($searchValue),
                default       => false
            };
        }
        // Handle amount field
        $ruleValue = (float) $this->value;

        return match ($this->operator) {
            'equals'       => abs($amount - $ruleValue) < 0.001,
            'greater_than' => $amount > $ruleValue,
            'less_than'    => $amount < $ruleValue,
            default        => false
        };

    }

    public function getDisplayName(): string
    {
        $operator = match ($this->operator) {
            'contains'     => 'contains',
            'equals'       => 'equals',
            'starts_with'  => 'starts with',
            'ends_with'    => 'ends with',
            'greater_than' => '>',
            'less_than'    => '<',
            default        => $this->operator
        };

        $fieldName = $this->field === 'description' ? 'Description' : 'Amount';

        return "$fieldName $operator '$this->value'";
    }
}
