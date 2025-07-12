<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Models\RecurringPattern;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class RecurringService
{
    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function generateDueTransactions(?User $user = null): Collection
    {
        $query = RecurringPattern::active()->with(['account', 'category']);

        if ($user) {
            $query->whereHas('account', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $duePatterns = $query->get()->filter(fn ($pattern) => $pattern->isDue());
        $generatedTransactions = collect();

        DB::transaction(function () use ($duePatterns, &$generatedTransactions) {
            foreach ($duePatterns as $pattern) {
                $transaction = $this->generateTransactionFromPattern($pattern);
                if ($transaction) {
                    $generatedTransactions->push($transaction);
                }
            }
        });

        return $generatedTransactions;
    }

    public function generateTransactionFromPattern(RecurringPattern $pattern): ?Transaction
    {
        if (! $pattern->isDue()) {
            return null;
        }

        $nextDueDate = $pattern->getNextDueDate();

        // Check if end date has passed
        if ($pattern->end_date && $nextDueDate->isAfter($pattern->end_date)) {
            $pattern->update(['is_active' => false]);

            return null;
        }

        $transactionData = [
            'account_id'             => $pattern->account_id,
            'type'                   => $pattern->type,
            'amount'                 => $pattern->amount,
            'description'            => $pattern->description,
            'transaction_date'       => $nextDueDate->toDateString(),
            'category_id'            => $pattern->category_id,
            'transfer_to_account_id' => $pattern->transfer_to_account_id,
            'recurring_pattern_id'   => $pattern->id,
        ];

        $transaction = $this->transactionService->createTransaction(
            $pattern->account->user,
            $transactionData
        );

        $pattern->update(['last_generated_date' => $nextDueDate]);

        return $transaction;
    }

    public function getUpcomingTransactions(User $user, int $days = 30): Collection
    {
        $endDate = Carbon::now()->addDays($days);
        $upcomingTransactions = collect();

        $activePatterns = RecurringPattern::active()
            ->whereHas('account', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['account', 'category', 'transferToAccount'])
            ->get();

        foreach ($activePatterns as $pattern) {
            $nextDueDate = $pattern->getNextDueDate();

            while ($nextDueDate->lte($endDate)) {
                // Check if end date has passed
                if ($pattern->end_date && $nextDueDate->isAfter($pattern->end_date)) {
                    break;
                }

                $upcomingTransactions->push((object) [
                    'id'                     => null,
                    'account_id'             => $pattern->account_id,
                    'type'                   => $pattern->type,
                    'amount'                 => $pattern->amount,
                    'description'            => $pattern->description.' (Recurring)',
                    'transaction_date'       => $nextDueDate->copy(),
                    'category_id'            => $pattern->category_id,
                    'transfer_to_account_id' => $pattern->transfer_to_account_id,
                    'recurring_pattern_id'   => $pattern->id,
                    'account'                => $pattern->account,
                    'category'               => $pattern->category,
                    'transferToAccount'      => $pattern->transferToAccount,
                    'recurringPattern'       => $pattern,
                    'signed_amount'          => match ($pattern->type) {
                        'income'   => (float) $pattern->amount,
                        'expense'  => -(float) $pattern->amount,
                        'transfer' => -(float) $pattern->amount,
                        default    => 0
                    },
                    'is_projected' => true,
                ]);

                // Calculate next occurrence
                $nextDueDate = match ($pattern->frequency) {
                    'daily'     => $nextDueDate->addDays($pattern->frequency_interval),
                    'weekly'    => $nextDueDate->addWeeks($pattern->frequency_interval),
                    'bi-weekly' => $nextDueDate->addWeeks(2 * $pattern->frequency_interval),
                    'monthly'   => $nextDueDate->addMonths($pattern->frequency_interval),
                    'yearly'    => $nextDueDate->addYears($pattern->frequency_interval),
                    default     => $nextDueDate->addDays($pattern->frequency_interval)
                };
            }
        }

        return $upcomingTransactions->sortBy('transaction_date')->values();
    }

    public function getTransactionsForDate(Account $account, Carbon $date): Collection
    {
        $patterns = RecurringPattern::active()
            ->where('account_id', $account->id)
            ->with(['category', 'transferToAccount'])
            ->get();

        $transactions = collect();

        foreach ($patterns as $pattern) {
            if ($this->isPatternDueOnDate($pattern, $date)) {
                $transactions->push((object) [
                    'type'              => $pattern->type,
                    'amount'            => $pattern->amount,
                    'description'       => $pattern->description.' (Recurring)',
                    'category'          => $pattern->category,
                    'transferToAccount' => $pattern->transferToAccount,
                    'signed_amount'     => match ($pattern->type) {
                        'income'   => (float) $pattern->amount,
                        'expense'  => -(float) $pattern->amount,
                        'transfer' => -(float) $pattern->amount,
                        default    => 0
                    },
                    'is_projected' => true,
                ]);
            }
        }

        return $transactions;
    }

    public function previewNextOccurrences(RecurringPattern $pattern, int $count = 5): Collection
    {
        $occurrences = collect();
        $nextDate = $pattern->getNextDueDate();

        for ($i = 0; $i < $count; $i++) {
            if ($pattern->end_date && $nextDate->isAfter($pattern->end_date)) {
                break;
            }

            $occurrences->push([
                'date'             => $nextDate->copy(),
                'amount'           => $pattern->amount,
                'description'      => $pattern->description,
                'is_past_end_date' => $pattern->end_date && $nextDate->isAfter($pattern->end_date),
            ]);

            $nextDate = match ($pattern->frequency) {
                'daily'     => $nextDate->addDays($pattern->frequency_interval),
                'weekly'    => $nextDate->addWeeks($pattern->frequency_interval),
                'bi-weekly' => $nextDate->addWeeks(2 * $pattern->frequency_interval),
                'monthly'   => $nextDate->addMonths($pattern->frequency_interval),
                'yearly'    => $nextDate->addYears($pattern->frequency_interval),
                default     => $nextDate->addDays($pattern->frequency_interval)
            };
        }

        return $occurrences;
    }

    public function skipNextOccurrence(RecurringPattern $pattern): bool
    {
        $nextDueDate = $pattern->getNextDueDate();
        $pattern->update(['last_generated_date' => $nextDueDate]);

        return true;
    }

    public function pausePattern(RecurringPattern $pattern): bool
    {
        return $pattern->update(['is_active' => false]);
    }

    public function resumePattern(RecurringPattern $pattern): bool
    {
        return $pattern->update(['is_active' => true]);
    }

    private function isPatternDueOnDate(RecurringPattern $pattern, Carbon $date): bool
    {
        $startDate = $pattern->last_generated_date ?? $pattern->start_date;

        if ($date->lt($startDate)) {
            return false;
        }

        if ($pattern->end_date && $date->gt($pattern->end_date)) {
            return false;
        }

        $daysDiff = $startDate->diffInDays($date);

        return match ($pattern->frequency) {
            'daily'     => $daysDiff % $pattern->frequency_interval === 0,
            'weekly'    => $daysDiff % (7 * $pattern->frequency_interval) === 0 && $date->dayOfWeek === $startDate->dayOfWeek,
            'bi-weekly' => $daysDiff % (14 * $pattern->frequency_interval) === 0 && $date->dayOfWeek === $startDate->dayOfWeek,
            'monthly'   => $date->day === $startDate->day && $daysDiff >= (30 * $pattern->frequency_interval),
            'yearly'    => $date->month === $startDate->month && $date->day === $startDate->day && $daysDiff >= (365 * $pattern->frequency_interval),
            default     => false
        };
    }
}
