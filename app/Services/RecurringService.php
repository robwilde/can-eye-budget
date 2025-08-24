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

    /**
     * Get available frequency options for the UI dropdown
     */
    public function getAvailableFrequencies(): array
    {
        return [
            'dont-repeat'      => "Don't repeat",
            'everyday'         => 'Everyday',
            'every-week'       => 'Every week',
            'every-month'      => 'Every month',
            'every-3-months'   => 'Every 3 months',
            'every-6-months'   => 'Every 6 months',
            'every-year'       => 'Every year',
            'every-workday'    => 'Every workday',
            'every-weekend'    => 'Every weekend',
            '2-on-2-off'       => '2 on 2 off',
            'every-2-days'     => 'Every 2 days',
            'every-3-days'     => 'Every 3 days',
            'every-4-days'     => 'Every 4 days',
            'every-5-days'     => 'Every 5 days',
            'every-6-days'     => 'Every 6 days',
            'every-2-weeks'    => 'Every 2 weeks',
            'every-3-weeks'    => 'Every 3 weeks',
            'every-4-weeks'    => 'Every 4 weeks',
            'every-1-5-months' => 'Every 1.5 months',
        ];
    }

    /**
     * Get frequency duration options for the UI dropdown
     */
    public function getFrequencyDurationOptions(): array
    {
        return [
            'always'     => 'Always',
            'until-date' => 'Until date',
        ];
    }

    /**
     * Create multiple transactions based on frequency and end date
     */
    public function createRecurringTransactions(
        User $user,
        array $transactionData,
        string $frequency,
        ?string $endDate = null
    ): Collection {
        if ($frequency === 'dont-repeat') {
            $transaction = $this->transactionService->createTransaction($user, $transactionData);

            return collect([$transaction]);
        }

        $transactions = collect();
        $startDate = Carbon::parse($transactionData['transaction_date']);
        $endDate = $endDate ? Carbon::parse($endDate) : null;

        $currentDate = $startDate->copy();
        $count = 0;
        $maxTransactions = 100; // Safety limit

        // If no end date is specified, create transactions for the next year
        if ($endDate === null) {
            $endDate = $startDate->copy()->addYear();
        }

        while ($currentDate->lte($endDate) && $count < $maxTransactions) {
            $currentTransactionData = $transactionData;
            $currentTransactionData['transaction_date'] = $currentDate->format('Y-m-d');

            $transaction = $this->transactionService->createTransaction($user, $currentTransactionData);
            $transactions->push($transaction);

            $count++;

            // Calculate next occurrence
            $nextDate = $this->getNextOccurrenceFromFrequency($currentDate, $frequency);

            // Break if next date would exceed end date
            if ($nextDate->gt($endDate)) {
                break;
            }

            $currentDate = $nextDate;
        }

        return $transactions;
    }

    /**
     * Get next occurrence based on UI frequency string
     */
    public function getNextOccurrenceFromFrequency(Carbon $date, string $frequency): Carbon
    {
        return match ($frequency) {
            'everyday'         => $date->copy()->addDay(),
            'every-week'       => $date->copy()->addWeek(),
            'every-2-weeks'    => $date->copy()->addWeeks(2),
            'every-3-weeks'    => $date->copy()->addWeeks(3),
            'every-4-weeks'    => $date->copy()->addWeeks(4),
            'every-month'      => $date->copy()->addMonth(),
            'every-3-months'   => $date->copy()->addMonths(3),
            'every-6-months'   => $date->copy()->addMonths(6),
            'every-year'       => $date->copy()->addYear(),
            'every-workday'    => $this->getNextWorkday($date),
            'every-weekend'    => $this->getNextWeekend($date),
            '2-on-2-off'       => $date->copy()->addDays(2),
            'every-2-days'     => $date->copy()->addDays(2),
            'every-3-days'     => $date->copy()->addDays(3),
            'every-4-days'     => $date->copy()->addDays(4),
            'every-5-days'     => $date->copy()->addDays(5),
            'every-6-days'     => $date->copy()->addDays(6),
            'every-1-5-months' => $date->copy()->addDays(45), // 1.5 months ≈ 45 days
            default            => $date->copy()->addDay(),
        };
    }

    /**
     * Calculate how many transactions would be created
     */
    public function calculateOccurrenceCount(
        string $startDate,
        string $frequency,
        ?string $endDate = null
    ): int {
        if (! $endDate || $frequency === 'dont-repeat') {
            return $frequency === 'dont-repeat' ? 1 : 0; // 0 means unlimited
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $count = 0;
        $current = $start->copy();
        $maxCount = 1000; // Safety limit

        while ($current->lte($end) && $count < $maxCount) {
            $count++;
            $current = $this->getNextOccurrenceFromFrequency($current, $frequency);
        }

        return $count;
    }

    private function getNextWorkday(Carbon $date): Carbon
    {
        $next = $date->copy()->addDay();

        // Skip weekends
        while ($next->isWeekend()) {
            $next->addDay();
        }

        return $next;
    }

    private function getNextWeekend(Carbon $date): Carbon
    {
        $next = $date->copy()->addDay();

        // Find next weekend day (Saturday or Sunday)
        while (! $next->isWeekend()) {
            $next->addDay();
        }

        return $next;
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
