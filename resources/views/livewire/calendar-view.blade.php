<div class="space-y-6">
    {{-- Header with Navigation --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $this->getViewTitle() }}
            </h1>

            {{-- Account Filter --}}
            <flux:select wire:model.live="selectedAccountId" placeholder="All Accounts" variant="minimal">
                <flux:select.option value="">All Accounts</flux:select.option>
                @foreach($this->accounts as $account)
                    <flux:select.option value="{{ $account->id }}">{{ $account->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        {{-- Navigation Controls --}}
        <div class="flex items-center gap-4">
            {{-- Add Transaction Button --}}
            <flux:button wire:click="openTransactionForm" variant="primary" size="sm" icon="plus">
                Add Transaction
            </flux:button>

            {{-- Period Navigation --}}
            <div class="flex items-center gap-1">
                <flux:button wire:click="previousPeriod" variant="ghost" size="sm" icon="chevron-left">
                    Previous
                </flux:button>
                <flux:button wire:click="goToToday" variant="ghost" size="sm">
                    Today
                </flux:button>
                <flux:button wire:click="nextPeriod" variant="ghost" size="sm" icon="chevron-right" icon-trailing>
                    Next
                </flux:button>
            </div>

            {{-- View Switcher --}}
            <div class="flex rounded-lg border border-gray-200 dark:border-gray-700">
                <flux:button
                    wire:click="setView('day')"
                    variant="{{ $view === 'day' ? 'primary' : 'ghost' }}"
                    size="sm"
                    class="rounded-r-none"
                >
                    Day
                </flux:button>
                <flux:button
                    wire:click="setView('week')"
                    variant="{{ $view === 'week' ? 'primary' : 'ghost' }}"
                    size="sm"
                    class="rounded-none border-x-0"
                >
                    Week
                </flux:button>
                <flux:button
                    wire:click="setView('month')"
                    variant="{{ $view === 'month' ? 'primary' : 'ghost' }}"
                    size="sm"
                    class="rounded-none border-x-0"
                >
                    Month
                </flux:button>
                <flux:button
                    wire:click="setView('year')"
                    variant="{{ $view === 'year' ? 'primary' : 'ghost' }}"
                    size="sm"
                    class="rounded-l-none"
                >
                    Year
                </flux:button>
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
                    $projectedChange = $balance['projected'] - $balance['current'];
                @endphp

                <flux:card class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium text-gray-900 dark:text-white">{{ $account->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($account->type) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold {{ $isPositive ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                ${{ number_format($balance['current'], 2) }}
                            </p>
                            @if($projectedChange !== 0)
                                <p class="text-sm {{ $projectedChange > 0 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $projectedChange > 0 ? '+' : '' }}${{ number_format($projectedChange, 2) }} projected
                                </p>
                            @endif
                        </div>
                    </div>
                </flux:card>
            @endif
        @endforeach
    </div>

    {{-- Calendar Content Based on View --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        @if($view === 'day')
            @include('livewire.calendar.day-view')
        @elseif($view === 'week')
            @include('livewire.calendar.week-view')
        @elseif($view === 'month')
            @include('livewire.calendar.month-view')
        @elseif($view === 'year')
            @include('livewire.calendar.year-view')
        @endif
    </div>
</div>
