<div class="space-y-6">
    {{-- Header with Navigation --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $this->getViewTitle() }}
            </h1>
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

    {{-- Period Totals Summary --}}
    <div class="grid gap-4 md:grid-cols-2">
        {{-- Planned Totals --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 text-center">Planned</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Incomes</span>
                    <span class="text-green-600 font-medium">+${{ number_format($this->periodTotals['planned']['income'], 0, '.', ',') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Expenses</span>
                    <span class="text-red-600 font-medium">-${{ number_format($this->periodTotals['planned']['expenses'], 0, '.', ',') }}</span>
                </div>
                <hr class="border-gray-200 dark:border-gray-600">
                <div class="flex justify-between items-center font-semibold">
                    <span class="text-gray-900 dark:text-white">Net</span>
                    <span class="text-green-600">+${{ number_format($this->periodTotals['planned']['net'], 0, '.', ',') }}</span>
                </div>
            </div>
        </div>

        {{-- Entered Totals --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 relative">
            {{-- Percentage Saved Badge --}}
            <div class="absolute -top-3 -right-3 bg-green-100 text-green-800 text-xl font-bold px-3 py-1 rounded-full">
                {{ $this->periodTotals['percentage_saved'] }}% saved
            </div>
            
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 text-center">Entered</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Incomes</span>
                    <span class="text-green-600 font-medium">+${{ number_format($this->periodTotals['entered']['income'], 0, '.', ',') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Expenses</span>
                    <span class="text-red-600 font-medium">-${{ number_format($this->periodTotals['entered']['expenses'], 0, '.', ',') }}</span>
                </div>
                <hr class="border-gray-200 dark:border-gray-600">
                <div class="flex justify-between items-center font-semibold">
                    <span class="text-gray-900 dark:text-white">Net</span>
                    <span class="text-green-600">+${{ number_format($this->periodTotals['entered']['net'], 0, '.', ',') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Calendar Views Based on Selector --}}
    {{-- Week Calendar View --}}
    @if($view === 'week')
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Week Calendar</h3>
            <div class="grid grid-cols-7 gap-2">
                @php
                    $dateRange = $this->getDateRange();
                    $startDate = $dateRange['start'];
                    $currentDate = $startDate->copy();
                @endphp
                
                {{-- Day headers --}}
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                    <div class="text-center text-sm font-medium text-gray-500 dark:text-gray-400 py-2">
                        {{ $dayName }}
                    </div>
                @endforeach
                
                {{-- Calendar days --}}
                @for($i = 0; $i < 7; $i++)
                    @php
                        $dayDate = $currentDate->format('Y-m-d');
                        $dayTransactions = $this->transactions[$dayDate] ?? collect();
                        $hasTransactions = $dayTransactions->isNotEmpty();
                    @endphp
                    
                    <div 
                        wire:click="openTransactionForDay('{{ $dayDate }}')"
                        class="min-h-24 border border-gray-200 dark:border-gray-600 rounded-lg p-2 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer {{ $currentDate->isToday() ? 'bg-blue-50 dark:bg-blue-900 border-blue-200 dark:border-blue-700' : '' }}"
                        title="Click to add new transaction"
                    >
                        <div 
                            wire:click.stop="selectDate('{{ $dayDate }}')"
                            class="text-sm font-medium text-gray-900 dark:text-white hover:font-bold hover:text-base cursor-pointer inline-block mb-1 transition-all"
                            title="Click for day view"
                        >
                            {{ $currentDate->format('j') }}
                        </div>
                        
                        {{-- Transaction indicators --}}
                        <div class="space-y-1 mt-1">
                            @foreach($dayTransactions->take(3) as $transaction)
                                @php
                                    $isIncome = $transaction->type === 'income';
                                    $isTransfer = $transaction->type === 'transfer';
                                    $isPlanned = $transaction->status === 'planned';
                                @endphp
                                
                                <div 
                                    wire:click.stop="openTransactionForm({{ $transaction->id }})"
                                    class="text-xs px-2 py-1 rounded text-white truncate cursor-pointer transition-colors
                                           @if($isIncome) 
                                               bg-green-600 hover:bg-green-700 {{ $isPlanned ? 'font-bold' : 'opacity-60' }}
                                           @elseif($isTransfer) 
                                               bg-orange-600 hover:bg-orange-700 {{ $isPlanned ? 'font-bold' : 'opacity-60' }}
                                           @else 
                                               bg-red-600 hover:bg-red-700 {{ $isPlanned ? 'font-bold' : 'opacity-60' }}
                                           @endif"
                                    title="Click to edit: {{ $transaction->description }}"
                                >
                                    ${{ number_format($transaction->amount, 0) }} {{ Str::limit($transaction->description, 12) }}
                                </div>
                            @endforeach
                            
                            @if($dayTransactions->count() > 3)
                                <div class="text-xs text-gray-500">+{{ $dayTransactions->count() - 3 }} more</div>
                            @endif
                        </div>
                    </div>
                    
                    @php $currentDate->addDay(); @endphp
                @endfor
            </div>
        </div>
    @endif

    {{-- Month Calendar View --}}
    @if($view === 'month')
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Month Calendar</h3>
            
            @php
                $dateRange = $this->getDateRange();
                $startDate = $dateRange['start']->startOfMonth()->startOfWeek(); // Start from Sunday
                $endDate = $dateRange['end']->endOfMonth()->endOfWeek(); // End on Saturday
                $currentDate = $startDate->copy();
                $weeksCount = $startDate->diffInWeeks($endDate) + 1;
            @endphp
            
            <div class="grid grid-cols-7 gap-1">
                {{-- Day headers --}}
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                    <div class="text-center text-sm font-medium text-gray-500 dark:text-gray-400 py-2">
                        {{ $dayName }}
                    </div>
                @endforeach
                
                {{-- Calendar grid --}}
                @for($week = 0; $week < $weeksCount; $week++)
                    @for($day = 0; $day < 7; $day++)
                        @php
                            $dayDate = $currentDate->format('Y-m-d');
                            $dayTransactions = $this->transactions[$dayDate] ?? collect();
                            $isCurrentMonth = $currentDate->month === $this->currentDate->month;
                            $isToday = $currentDate->isToday();
                        @endphp
                        
                        <div 
                            wire:click="openTransactionForDay('{{ $dayDate }}')"
                            class="min-h-20 border border-gray-200 dark:border-gray-600 rounded p-1 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer
                                {{ !$isCurrentMonth ? 'bg-gray-50 dark:bg-gray-700 text-gray-400' : '' }}
                                {{ $isToday ? 'bg-blue-50 dark:bg-blue-900 border-blue-200 dark:border-blue-700' : '' }}"
                            title="Click to add new transaction"
                        >
                            <div 
                                wire:click.stop="selectDate('{{ $dayDate }}')"
                                class="text-xs font-medium {{ $isCurrentMonth ? 'text-gray-900 dark:text-white' : 'text-gray-400' }} hover:font-bold hover:text-sm cursor-pointer inline-block mb-1 transition-all"
                                title="Click for day view"
                            >
                                {{ $currentDate->format('j') }}
                            </div>
                            
                            {{-- Transaction indicators for month view (compact) --}}
                            <div class="mt-1 space-y-0.5">
                                @foreach($dayTransactions->take(2) as $transaction)
                                    @php
                                        $isIncome = $transaction->type === 'income';
                                        $isTransfer = $transaction->type === 'transfer';
                                        $isPlanned = $transaction->status === 'planned';
                                    @endphp
                                    
                                    <div 
                                        wire:click.stop="openTransactionForm({{ $transaction->id }})"
                                        class="text-xs px-2 py-1 rounded text-white truncate cursor-pointer transition-colors
                                               @if($isIncome) 
                                                   bg-green-600 hover:bg-green-700 {{ $isPlanned ? 'font-bold' : 'opacity-60' }}
                                               @elseif($isTransfer) 
                                                   bg-orange-600 hover:bg-orange-700 {{ $isPlanned ? 'font-bold' : 'opacity-60' }}
                                               @else 
                                                   bg-red-600 hover:bg-red-700 {{ $isPlanned ? 'font-bold' : 'opacity-60' }}
                                               @endif" 
                                        title="Click to edit: {{ $transaction->description }}"
                                    >
                                        ${{ number_format($transaction->amount, 0) }}
                                        @if($transaction->description && strlen($transaction->description) > 0)
                                            {{ Str::limit($transaction->description, 8) }}
                                        @endif
                                    </div>
                                @endforeach
                                
                                @if($dayTransactions->count() > 2)
                                    <div class="text-xs text-gray-500">+{{ $dayTransactions->count() - 2 }} more</div>
                                @endif
                            </div>
                        </div>
                        
                        @php $currentDate->addDay(); @endphp
                    @endfor
                @endfor
            </div>
        </div>
    @endif

    {{-- Transaction List for Day and Year Views --}}
    @if($view === 'day' || $view === 'year')
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
                            $isPlanned = $transaction->status === 'planned';
                            $baseColor = $isIncome ? 'green' : ($isTransfer ? 'orange' : 'red');
                            $colorIntensity = $isPlanned ? '400' : '600'; // Lighter for planned, darker for entered
                        @endphp

                        <div 
                            wire:click="openTransactionForm({{ $transaction->id }})"
                            class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                {{-- Type and Status Indicator --}}
                                <div class="w-3 h-3 rounded-full bg-{{ $baseColor }}-{{ $colorIntensity }} {{ $isPlanned ? 'opacity-70' : '' }}"></div>

                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white {{ $isPlanned ? 'opacity-75' : '' }}">
                                        {{ $transaction->description }}
                                        @if($isPlanned) <span class="text-xs text-gray-500">(Planned)</span> @endif
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
                                @php
                                    $isTransferDestination = $isTransfer && $transaction->isTransferDestination();
                                @endphp
                                <p class="font-semibold text-{{ $baseColor }}-{{ $colorIntensity }} {{ $isPlanned ? 'opacity-75' : '' }}">
                                    {{ $isIncome || $isTransferDestination ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
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
    @endif
</div>