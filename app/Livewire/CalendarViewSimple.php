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
