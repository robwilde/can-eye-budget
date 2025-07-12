<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class CalendarViewSimple extends Component
{
    public string $view = 'month'; // 'day', 'week', 'month', 'year'

    public Carbon $currentDate;

    public ?int $selectedAccountId = null;

    public function mount(): void
    {
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

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function selectAccount(?int $accountId): void
    {
        $this->selectedAccountId = $accountId;
    }

    public function previousPeriod(): void
    {
        $this->currentDate = match ($this->view) {
            'day'   => $this->currentDate->subDay(),
            'week'  => $this->currentDate->subWeek(),
            'year'  => $this->currentDate->subYear(),
            default => $this->currentDate->subMonth(),
        };
    }

    public function nextPeriod(): void
    {
        $this->currentDate = match ($this->view) {
            'day'   => $this->currentDate->addDay(),
            'week'  => $this->currentDate->addWeek(),
            'year'  => $this->currentDate->addYear(),
            default => $this->currentDate->addMonth(),
        };
    }

    public function goToToday(): void
    {
        $this->currentDate = Carbon::now();
    }

    public function getViewTitle(): string
    {
        return match ($this->view) {
            'day'  => $this->currentDate->format('F j, Y'),
            'week' => 'Week of '.$this->currentDate
                    ->startOfWeek()
                    ->format('M j').' - '.$this->currentDate
                    ->endOfWeek()
                    ->format('M j, Y'),
            'year'  => $this->currentDate->format('Y'),
            default => $this->currentDate->format('F Y'),
        };
    }

    public function openTransactionForm(?int $transactionId = null): void
    {
        $this->dispatch('open-transaction-form', $transactionId);
    }

    public function openTransactionFormForDate(string $date): void
    {
        $this->dispatch('open-transaction-form-for-date', $date);
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
        $date = $this->currentDate->copy();

        return match ($this->view) {
            'day' => [
                'start' => $date->startOfDay(),
                'end'   => $date->copy()->endOfDay(),
            ],
            'week' => [
                'start' => $date->startOfWeek(),
                'end'   => $date->copy()->endOfWeek(),
            ],
            'year' => [
                'start' => $date->startOfYear(),
                'end'   => $date->copy()->endOfYear(),
            ],
            default => [
                'start' => $date->startOfMonth(),
                'end'   => $date->copy()->endOfMonth(),
            ],
        };
    }
}
