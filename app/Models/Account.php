<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'initial_balance',
        'currency',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
            ->sum('amount') ?? 0;

        return (float) ($this->initial_balance + $transactionSum + $transfersInSum);
    }
}
