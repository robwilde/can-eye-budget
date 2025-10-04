<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Helpers\CalendarHelper;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class CalendarViewSimple extends Component
{
    public string $view {
        set {
            $this->view = $value;
        }
    }

    public Carbon $currentDate;

    public ?int $selectedAccountId = null;

    public function mount(): void
    {
        $this->view = config('app.dashboard.view');
        $this->currentDate = Carbon::now();
    }

    #[Computed]
    public function accounts()
    {
        return auth()
            ->user()
            ->accounts()
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function selectedAccount()
    {
        if (! $this->selectedAccountId) {
            return null;
        }

        return $this->accounts->firstWhere('id', $this->selectedAccountId);
    }

    #[Computed]
    public function transactions(): Collection
    {
        $dateRange = $this->getDateRange();

        $query = auth()
            ->user()
            ->accounts()
            ->with([
                'transactions' => function ($query) use ($dateRange) {
                    $query
                        ->with(['category', 'transferToAccount'])
                        ->forDateRange($dateRange['start'], $dateRange['end'])
                        ->orderBy('transaction_date', 'desc')
                        ->orderBy('created_at', 'desc');
                },
            ]);

        if ($this->selectedAccountId) {
            $query->where('id', $this->selectedAccountId);
        }

        return $query
            ->get()
            ->flatMap(fn ($account) => $account->transactions)
            ->groupBy(fn ($transaction) => $transaction->transaction_date->format('Y-m-d'));
    }

    #[Computed]
    public function balances(): array
    {
        $balances = [];

        foreach ($this->accounts as $account) {
            if ($this->selectedAccount) {
                $balance = $this->selectedAccount->id === $account->id
                    ? $account->getCurrentBalance()
                    : 0;
            } else {
                $balance = $account->getCurrentBalance();
            }

            $balances[$account->id] = [
                'current' => $balance,
            ];
        }

        return $balances;
    }

    #[Computed]
    public function periodTotals(): array
    {
        $allTransactions = $this->transactions->flatten();

        $plannedIncome = 0;
        $plannedExpenses = 0;
        $enteredIncome = 0;
        $enteredExpenses = 0;

        foreach ($allTransactions as $transaction) {
            $amount = $transaction->amount;
            $isPlanned = $transaction->status === 'planned';

            if ($transaction->type === 'income') {
                if ($isPlanned) {
                    $plannedIncome += $amount;
                } else {
                    $enteredIncome += $amount;
                }
            } elseif ($transaction->type === 'expense') {
                if ($isPlanned) {
                    $plannedExpenses += $amount;
                } else {
                    $enteredExpenses += $amount;
                }
            }
            // Skip transfers for income/expense totals as they're neutral to net worth
        }

        $plannedNet = $plannedIncome - $plannedExpenses;
        $enteredNet = $enteredIncome - $enteredExpenses;

        // Calculate percentage saved (how much of planned net was actually achieved)
        $percentageSaved = $plannedNet > 0 ? round(($enteredNet / $plannedNet) * 100) : 0;

        return [
            'planned' => [
                'income'   => $plannedIncome,
                'expenses' => $plannedExpenses,
                'net'      => $plannedNet,
            ],
            'entered' => [
                'income'   => $enteredIncome,
                'expenses' => $enteredExpenses,
                'net'      => $enteredNet,
            ],
            'percentage_saved' => $percentageSaved,
        ];
    }

    public function setView(string $view): void
    {
        $this->view = $view;
        $this->dispatch('view-changed', $view);
    }

    public function selectAccount(?int $accountId): void
    {
        $this->selectedAccountId = $accountId;
    }

    public function previousPeriod(): void
    {
        $this->currentDate = CalendarHelper::getPreviousPeriod($this->currentDate, $this->view);
    }

    public function nextPeriod(): void
    {
        $this->currentDate = CalendarHelper::getNextPeriod($this->currentDate, $this->view);
    }

    public function goToToday(): void
    {
        $this->currentDate = Carbon::now();
    }

    public function getViewTitle(): string
    {
        return CalendarHelper::getViewTitle($this->currentDate, $this->view);
    }

    public function openTransactionForm(?int $transactionId = null): void
    {
        $this->dispatch('open-transaction-form', $transactionId);
    }

    public function openTransactionFormForDate(string $date): void
    {
        $this->dispatch('open-transaction-form-for-date', $date);
    }

    public function openTransactionForDay(string $date): void
    {
        $this->dispatch('open-transaction-form-for-date', $date);
    }

    public function selectDate(string $date): void
    {
        $this->currentDate = Carbon::parse($date);
        $this->setView('day');
    }

    public function selectMonth(string $date): void
    {
        $this->currentDate = Carbon::parse($date);
        $this->setView('month');
    }

    public function render(): View
    {
        return view('livewire.calendar-view-simple');
    }

    // Event listeners for transaction updates
    protected function getListeners(): array
    {
        return [
            'transaction-created' => '$refresh',
            'transaction-updated' => '$refresh',
            'transaction-deleted' => '$refresh',
        ];
    }

    private function getDateRange(): array
    {
        return CalendarHelper::getDateRange($this->currentDate, $this->view);
    }
}
