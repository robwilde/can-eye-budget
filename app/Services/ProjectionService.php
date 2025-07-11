<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProjectionService
{
    private RecurringService $recurringService;

    public function __construct(RecurringService $recurringService)
    {
        $this->recurringService = $recurringService;
    }

    public function calculateProjections(User $user, Carbon $endDate, ?Account $account = null): array
    {
        $accounts = $account ? collect([$account]) : $user->accounts;
        $projections = [];

        foreach ($accounts as $acc) {
            $projections[$acc->id] = $this->calculateAccountProjections($acc, $endDate);
        }

        return $projections;
    }

    public function calculateAccountProjections(Account $account, Carbon $endDate): array
    {
        $currentDate = Carbon::now()->startOfDay();
        $projections = [];
        $runningBalance = $account->getCurrentBalance();

        while ($currentDate->lte($endDate)) {
            $dailyBalance = $this->calculateDailyBalance($account, $currentDate, $runningBalance);

            $projections[] = [
                'date' => $currentDate->copy(),
                'balance' => $dailyBalance['balance'],
                'income' => $dailyBalance['income'],
                'expenses' => $dailyBalance['expenses'],
                'net' => $dailyBalance['net'],
                'is_negative' => $dailyBalance['balance'] < 0,
                'transactions' => $dailyBalance['transactions'],
            ];

            $runningBalance = $dailyBalance['balance'];
            $currentDate->addDay();
        }

        return $projections;
    }

    public function findNegativeBalanceDates(Account $account, Carbon $endDate): Collection
    {
        $projections = $this->calculateAccountProjections($account, $endDate);

        return collect($projections)
            ->filter(fn ($projection) => $projection['is_negative'])
            ->pluck('date');
    }

    public function calculateMonthlyProjections(User $user, int $months = 12): array
    {
        $startDate = Carbon::now()->startOfMonth();
        $monthlyData = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $startDate->copy()->addMonths($i);
            $endOfMonth = $month->copy()->endOfMonth();

            $monthData = [
                'month' => $month->format('Y-m'),
                'month_name' => $month->format('F Y'),
                'accounts' => [],
                'total_balance' => 0,
                'total_income' => 0,
                'total_expenses' => 0,
            ];

            foreach ($user->accounts as $account) {
                $accountProjections = $this->calculateAccountProjections($account, $endOfMonth);
                $lastDay = end($accountProjections);

                if ($lastDay) {
                    $monthData['accounts'][$account->id] = [
                        'name' => $account->name,
                        'balance' => $lastDay['balance'],
                        'income' => collect($accountProjections)->sum('income'),
                        'expenses' => collect($accountProjections)->sum('expenses'),
                    ];

                    $monthData['total_balance'] += $lastDay['balance'];
                    $monthData['total_income'] += $monthData['accounts'][$account->id]['income'];
                    $monthData['total_expenses'] += $monthData['accounts'][$account->id]['expenses'];
                }
            }

            $monthlyData[] = $monthData;
        }

        return $monthlyData;
    }

    public function getUpcomingTransactions(User $user, int $days = 30): Collection
    {
        $endDate = Carbon::now()->addDays($days);

        // Get scheduled transactions
        $scheduledTransactions = Transaction::whereHas('account', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where('transaction_date', '>', Carbon::now())
            ->where('transaction_date', '<=', $endDate)
            ->with(['account', 'category', 'recurringPattern'])
            ->orderBy('transaction_date')
            ->get();

        // Get recurring transactions that are due
        $recurringTransactions = $this->recurringService->getUpcomingTransactions($user, $days);

        return $scheduledTransactions->merge($recurringTransactions)
            ->sortBy('transaction_date')
            ->values();
    }

    private function calculateDailyBalance(Account $account, Carbon $date, float $startingBalance): array
    {
        // Get confirmed transactions for this date
        $confirmedTransactions = Transaction::where('account_id', $account->id)
            ->whereDate('transaction_date', $date)
            ->with(['category', 'transferToAccount'])
            ->get();

        // Get transfers coming into this account
        $incomingTransfers = Transaction::where('transfer_to_account_id', $account->id)
            ->where('type', 'transfer')
            ->whereDate('transaction_date', $date)
            ->with(['account', 'category'])
            ->get();

        // Get projected recurring transactions for this date
        $recurringTransactions = $this->recurringService->getTransactionsForDate($account, $date);

        $allTransactions = $confirmedTransactions
            ->merge($incomingTransfers->map(function ($transfer) {
                return (object) [
                    'type' => 'transfer_in',
                    'amount' => $transfer->amount,
                    'description' => "Transfer from {$transfer->account->name}",
                    'category' => $transfer->category,
                    'signed_amount' => (float) $transfer->amount,
                ];
            }))
            ->merge($recurringTransactions);

        $income = $allTransactions
            ->where('type', 'income')
            ->sum('amount');

        $expenses = $allTransactions
            ->whereIn('type', ['expense', 'transfer'])
            ->sum('amount');

        $transfersIn = $allTransactions
            ->where('type', 'transfer_in')
            ->sum('amount');

        $net = $income + $transfersIn - $expenses;
        $balance = $startingBalance + $net;

        return [
            'balance' => $balance,
            'income' => $income + $transfersIn,
            'expenses' => $expenses,
            'net' => $net,
            'transactions' => $allTransactions,
        ];
    }
}
