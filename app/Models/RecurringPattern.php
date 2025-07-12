<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class RecurringPattern extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'amount',
        'description',
        'category_id',
        'transfer_to_account_id',
        'frequency',
        'frequency_interval',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'amount'              => 'decimal:2',
        'start_date'          => 'date',
        'end_date'            => 'date',
        'last_generated_date' => 'date',
        'is_active'           => 'boolean',
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

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDue($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('last_generated_date')
                    ->orWhere('last_generated_date', '<', $this->getNextDueDate());
            });
    }

    public function getNextDueDate(): Carbon
    {
        $lastGenerated = $this->last_generated_date ?? $this->start_date;

        return match ($this->frequency) {
            'daily'     => $lastGenerated->addDays($this->frequency_interval),
            'weekly'    => $lastGenerated->addWeeks($this->frequency_interval),
            'bi-weekly' => $lastGenerated->addWeeks(2 * $this->frequency_interval),
            'monthly'   => $lastGenerated->addMonths($this->frequency_interval),
            'yearly'    => $lastGenerated->addYears($this->frequency_interval),
            default     => $lastGenerated->addDays($this->frequency_interval)
        };
    }

    public function isDue(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->end_date && Carbon::now()->isAfter($this->end_date)) {
            return false;
        }

        $nextDue = $this->getNextDueDate();

        return Carbon::now()->isSameDay($nextDue) || Carbon::now()->isAfter($nextDue);
    }

    public function createTransaction(): Transaction
    {
        $transaction = Transaction::create([
            'account_id'             => $this->account_id,
            'type'                   => $this->type,
            'amount'                 => $this->amount,
            'description'            => $this->description,
            'transaction_date'       => $this->getNextDueDate(),
            'category_id'            => $this->category_id,
            'transfer_to_account_id' => $this->transfer_to_account_id,
            'recurring_pattern_id'   => $this->id,
        ]);

        $this->update(['last_generated_date' => $this->getNextDueDate()]);

        return $transaction;
    }
}
