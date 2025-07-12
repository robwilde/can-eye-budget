<?php

use App\Models\Account;
use App\Services\ProjectionService;
use App\Services\TransactionService;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component
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
        return auth()->user()->accounts()->orderBy('name')->get();
    }

    #[Computed]
    public function selectedAccount()
    {
        if (!$this->selectedAccountId) {
            return null;
        }
        
        return $this->accounts->firstWhere('id', $this->selectedAccountId);
    }

    #[Computed]
    public function transactions()
    {
        $dateRange = $this->getDateRange();
        
        $query = auth()->user()->accounts()
            ->with(['transactions' => function ($query) use ($dateRange) {
                $query->with(['category', 'transferToAccount'])
                    ->forDateRange($dateRange['start'], $dateRange['end'])
                    ->orderBy('transaction_date', 'desc')
                    ->orderBy('created_at', 'desc');
            }]);

        if ($this->selectedAccountId) {
            $query->where('id', $this->selectedAccountId);
        }

        return $query->get()
            ->flatMap(fn($account) => $account->transactions)
            ->groupBy(fn($transaction) => $transaction->transaction_date->format('Y-m-d'));
    }

    #[Computed]
    public function balances()
    {
        $balances = [];

        foreach ($this->accounts as $account) {
            $balance = $this->selectedAccount 
                ? ($this->selectedAccount->id === $account->id ? $account->getCurrentBalance() : 0)
                : $account->getCurrentBalance();
            
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
            'day' => $this->currentDate->subDay(),
            'week' => $this->currentDate->subWeek(),
            'month' => $this->currentDate->subMonth(),
            'year' => $this->currentDate->subYear(),
            default => $this->currentDate->subMonth(),
        };
    }

    public function nextPeriod(): void
    {
        $this->currentDate = match ($this->view) {
            'day' => $this->currentDate->addDay(),
            'week' => $this->currentDate->addWeek(),
            'month' => $this->currentDate->addMonth(),
            'year' => $this->currentDate->addYear(),
            default => $this->currentDate->addMonth(),
        };
    }

    public function goToToday(): void
    {
        $this->currentDate = Carbon::now();
    }

    private function getDateRange(): array
    {
        return match ($this->view) {
            'day' => [
                'start' => $this->currentDate->copy()->startOfDay(),
                'end' => $this->currentDate->copy()->endOfDay(),
            ],
            'week' => [
                'start' => $this->currentDate->copy()->startOfWeek(),
                'end' => $this->currentDate->copy()->endOfWeek(),
            ],
            'month' => [
                'start' => $this->currentDate->copy()->startOfMonth(),
                'end' => $this->currentDate->copy()->endOfMonth(),
            ],
            'year' => [
                'start' => $this->currentDate->copy()->startOfYear(),
                'end' => $this->currentDate->copy()->endOfYear(),
            ],
            default => [
                'start' => $this->currentDate->copy()->startOfMonth(),
                'end' => $this->currentDate->copy()->endOfMonth(),
            ],
        };
    }

    public function getViewTitle(): string
    {
        return match ($this->view) {
            'day' => $this->currentDate->format('F j, Y'),
            'week' => 'Week of ' . $this->currentDate->startOfWeek()->format('M j') . ' - ' . $this->currentDate->endOfWeek()->format('M j, Y'),
            'month' => $this->currentDate->format('F Y'),
            'year' => $this->currentDate->format('Y'),
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

    // Event listeners for transaction updates
    protected function getListeners(): array
    {
        return [
            'transaction-created' => '$refresh',
            'transaction-updated' => '$refresh',
            'transaction-deleted' => '$refresh',
        ];
    }
}; ?>

<div class="space-y-6">
    {{-- Header with Navigation --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $this->getViewTitle() }}
            </h1>
            
            {{-- Account Filter --}}
            <select wire:model.live="selectedAccountId" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">All Accounts</option>
                @foreach($this->accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Navigation Controls --}}
        <div class="flex items-center gap-4">
            {{-- Add Transaction Button --}}
            <button wire:click="openTransactionForm" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">
                + Add Transaction
            </button>

            {{-- Period Navigation --}}
            <div class="flex items-center gap-1">
                <button wire:click="previousPeriod" class="px-3 py-2 text-sm border rounded hover:bg-gray-50">
                    ← Previous
                </button>
                <button wire:click="goToToday" class="px-3 py-2 text-sm border rounded hover:bg-gray-50">
                    Today
                </button>
                <button wire:click="nextPeriod" class="px-3 py-2 text-sm border rounded hover:bg-gray-50">
                    Next →
                </button>
            </div>

            {{-- View Switcher --}}
            <div class="flex rounded-lg border border-gray-200">
                <button wire:click="setView('day')" class="px-3 py-2 text-sm {{ $view === 'day' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} rounded-l-lg">
                    Day
                </button>
                <button wire:click="setView('week')" class="px-3 py-2 text-sm {{ $view === 'week' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border-l border-r border-gray-200">
                    Week
                </button>
                <button wire:click="setView('month')" class="px-3 py-2 text-sm {{ $view === 'month' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border-r border-gray-200">
                    Month
                </button>
                <button wire:click="setView('year')" class="px-3 py-2 text-sm {{ $view === 'year' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} rounded-r-lg">
                    Year
                </button>
            </div>
        </div>
    </div>

    {{-- Account Balances Summary --}}
    <div class="grid gap-4 md:grid-cols-3">
        @foreach($this->accounts as $account)
            @if(!$selectedAccountId || $selectedAccountId === $account->id)
                @php
                    $balance = $this->balances[$account->id];
                    $isPositive = $balance['current'] >= 0;
                @endphp
                
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium text-gray-900 dark:text-white">{{ $account->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($account->type) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold {{ $isPositive ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                ${{ number_format($balance['current'], 2) }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    {{-- Simple Transaction List for Current Period --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
            Transactions for {{ $this->getViewTitle() }}
        </h3>
        
        @php
            $allTransactions = $this->transactions->flatten();
        @endphp
        
        @if($allTransactions->isNotEmpty())
            <div class="space-y-2">
                @foreach($allTransactions->take(20) as $transaction)
                    @php
                        $isIncome = $transaction->type === 'income';
                        $isTransfer = $transaction->type === 'transfer';
                    @endphp
                    
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
                        <div class="flex items-center gap-3">
                            {{-- Type Indicator --}}
                            <div class="w-3 h-3 rounded-full {{ $isIncome ? 'bg-green-500' : ($isTransfer ? 'bg-blue-500' : 'bg-red-500') }}"></div>
                            
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $transaction->description }}
                                </p>
                                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                    <span>{{ $transaction->account->name }}</span>
                                    @if($transaction->category)
                                        <span>•</span>
                                        <span>{{ $transaction->category->name }}</span>
                                    @endif
                                    <span>•</span>
                                    <span>{{ $transaction->transaction_date->format('M j, Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="font-semibold {{ $isIncome ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $isIncome ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($allTransactions->count() > 20)
                <p class="text-sm text-gray-500 mt-4 text-center">
                    Showing 20 of {{ $allTransactions->count() }} transactions
                </p>
            @endif
        @else
            <div class="text-center py-8">
                <p class="text-gray-500 dark:text-gray-400">No transactions found for this period</p>
                <button wire:click="openTransactionForm" class="mt-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                    Add your first transaction
                </button>
            </div>
        @endif
    </div>
</div>