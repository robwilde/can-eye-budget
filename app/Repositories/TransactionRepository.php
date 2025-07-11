<?php

namespace App\Repositories;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionRepository
{
    public function findForUser(User $user, array $filters = []): Builder
    {
        $query = Transaction::whereHas('account', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['account', 'category', 'transferToAccount', 'recurringPattern']);

        return $this->applyFilters($query, $filters);
    }

    public function findForAccount(Account $account, array $filters = []): Builder
    {
        $query = Transaction::where('account_id', $account->id)
            ->with(['category', 'transferToAccount', 'recurringPattern']);

        return $this->applyFilters($query, $filters);
    }

    public function getForCalendarView(User $user, Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->findForUser($user, [
            'date_range' => [$startDate, $endDate]
        ])
        ->orderBy('transaction_date')
        ->orderBy('created_at')
        ->get()
        ->groupBy(function ($transaction) {
            return $transaction->transaction_date->format('Y-m-d');
        });
    }

    public function getRecent(User $user, int $limit = 10): Collection
    {
        return $this->findForUser($user)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getPaginated(User $user, array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        return $this->findForUser($user, $filters)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getMonthlyTotals(User $user, int $year): array
    {
        $monthlySums = Transaction::whereHas('account', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereYear('transaction_date', $year)
            ->selectRaw('
                MONTH(transaction_date) as month,
                type,
                SUM(amount) as total
            ')
            ->groupBy('month', 'type')
            ->get();

        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyData[$month] = [
                'month' => $month,
                'month_name' => Carbon::create($year, $month)->format('F'),
                'income' => 0,
                'expenses' => 0,
                'net' => 0,
            ];
        }

        foreach ($monthlySums as $sum) {
            $month = $sum->month;
            if ($sum->type === 'income') {
                $monthlyData[$month]['income'] = (float) $sum->total;
            } else {
                $monthlyData[$month]['expenses'] += (float) $sum->total;
            }
        }

        // Calculate net for each month
        foreach ($monthlyData as &$data) {
            $data['net'] = $data['income'] - $data['expenses'];
        }

        return array_values($monthlyData);
    }

    public function getCategoryTotals(User $user, Carbon $startDate, Carbon $endDate): Collection
    {
        return Transaction::whereHas('account', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with('category')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('
                category_id,
                type,
                COUNT(*) as transaction_count,
                SUM(amount) as total_amount
            ')
            ->groupBy('category_id', 'type')
            ->get()
            ->groupBy('category_id');
    }

    public function getUnreconciledCount(Account $account): int
    {
        return $account->transactions()->unreconciled()->count();
    }

    public function searchTransactions(User $user, string $search): Collection
    {
        return $this->findForUser($user)
            ->where('description', 'like', "%{$search}%")
            ->orderBy('transaction_date', 'desc')
            ->limit(50)
            ->get();
    }

    public function getDuplicateCandidates(Account $account, Carbon $date, float $amount, string $description): Collection
    {
        return Transaction::where('account_id', $account->id)
            ->where('transaction_date', $date)
            ->where('amount', $amount)
            ->where('description', 'like', "%{$description}%")
            ->get();
    }

    public function bulkUpdateCategories(User $user, array $transactionIds, int $categoryId): int
    {
        return Transaction::whereHas('account', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereIn('id', $transactionIds)
            ->update(['category_id' => $categoryId]);
    }

    public function bulkReconcile(Account $account, array $transactionIds): int
    {
        return $account->transactions()
            ->whereIn('id', $transactionIds)
            ->update(['reconciled' => true]);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['date_range'])) {
            [$startDate, $endDate] = $filters['date_range'];
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        if (isset($filters['start_date'])) {
            $query->where('transaction_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('transaction_date', '<=', $filters['end_date']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }

        if (isset($filters['reconciled'])) {
            $query->where('reconciled', $filters['reconciled']);
        }

        if (isset($filters['min_amount'])) {
            $query->where('amount', '>=', $filters['min_amount']);
        }

        if (isset($filters['max_amount'])) {
            $query->where('amount', '<=', $filters['max_amount']);
        }

        if (isset($filters['search'])) {
            $query->where('description', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['recurring'])) {
            if ($filters['recurring']) {
                $query->whereNotNull('recurring_pattern_id');
            } else {
                $query->whereNull('recurring_pattern_id');
            }
        }

        return $query;
    }
}