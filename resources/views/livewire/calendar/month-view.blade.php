{{-- Month View --}}
<div class="p-6">
    @php
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();
        $startOfCalendar = $startOfMonth->copy()->startOfWeek();
        $endOfCalendar = $endOfMonth->copy()->endOfWeek();
        $days = [];
        
        for ($date = $startOfCalendar->copy(); $date->lte($endOfCalendar); $date->addDay()) {
            $days[] = $date->copy();
        }
        
        $weeks = array_chunk($days, 7);
    @endphp

    {{-- Month Header --}}
    <div class="mb-6">
        <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="bg-gray-50 dark:bg-gray-800 p-3 text-center">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $day }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Calendar Grid --}}
    <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden">
        @foreach($weeks as $week)
            @foreach($week as $day)
                @php
                    $dayKey = $day->format('Y-m-d');
                    $dayTransactions = $this->transactions[$dayKey] ?? collect();
                    $isCurrentMonth = $day->month === $currentDate->month;
                    $isToday = $day->isToday();
                    $dayIncome = $dayTransactions->where('type', 'income')->sum('amount');
                    $dayExpenses = $dayTransactions->whereIn('type', ['expense', 'transfer'])->sum('amount');
                    $dayNet = $dayIncome - $dayExpenses;
                @endphp
                
                <div 
                    class="bg-white dark:bg-gray-800 p-2 min-h-[120px] {{ !$isCurrentMonth ? 'opacity-50' : '' }} {{ $isToday ? 'ring-2 ring-blue-500' : '' }} hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors cursor-pointer"
                    wire:click="setView('day'); $set('currentDate', '{{ $day->toDateString() }}')"
                >
                    {{-- Day Number --}}
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium {{ $isToday ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $day->day }}
                        </span>
                        @if($dayTransactions->isNotEmpty())
                            <span class="text-xs text-gray-500 bg-gray-100 dark:bg-gray-700 px-1 rounded">
                                {{ $dayTransactions->count() }}
                            </span>
                        @endif
                    </div>

                    {{-- Day Summary --}}
                    @if($dayIncome > 0 || $dayExpenses > 0)
                        <div class="space-y-1">
                            @if($dayIncome > 0)
                                <div class="text-xs text-green-600 dark:text-green-400">
                                    +${{ number_format($dayIncome, 0) }}
                                </div>
                            @endif
                            @if($dayExpenses > 0)
                                <div class="text-xs text-red-600 dark:text-red-400">
                                    -${{ number_format($dayExpenses, 0) }}
                                </div>
                            @endif
                            @if($dayNet != 0)
                                <div class="text-xs font-medium {{ $dayNet > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} border-t border-gray-200 dark:border-gray-600 pt-1">
                                    {{ $dayNet > 0 ? '+' : '' }}${{ number_format($dayNet, 0) }}
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Transaction Dots --}}
                    @if($dayTransactions->isNotEmpty())
                        <div class="flex gap-1 mt-2 flex-wrap">
                            @foreach($dayTransactions->take(3) as $transaction)
                                <div 
                                    class="w-2 h-2 rounded-full {{ $transaction->type === 'income' ? 'bg-green-500' : ($transaction->type === 'transfer' ? 'bg-blue-500' : 'bg-red-500') }}"
                                    title="{{ $transaction->description }}"
                                ></div>
                            @endforeach
                            @if($dayTransactions->count() > 3)
                                <div class="text-xs text-gray-400">+{{ $dayTransactions->count() - 3 }}</div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        @endforeach
    </div>

    {{-- Month Summary --}}
    <div class="mt-6 bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
        @php
            $monthTransactions = $this->transactions->flatten();
            $monthIncome = $monthTransactions->where('type', 'income')->sum('amount');
            $monthExpenses = $monthTransactions->whereIn('type', ['expense', 'transfer'])->sum('amount');
            $monthNet = $monthIncome - $monthExpenses;
        @endphp
        
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Month Summary</h3>
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Income</p>
                <p class="text-xl font-semibold text-green-600 dark:text-green-400">
                    ${{ number_format($monthIncome, 2) }}
                </p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Expenses</p>
                <p class="text-xl font-semibold text-red-600 dark:text-red-400">
                    ${{ number_format($monthExpenses, 2) }}
                </p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Net</p>
                <p class="text-xl font-semibold {{ $monthNet >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ $monthNet >= 0 ? '+' : '' }}${{ number_format($monthNet, 2) }}
                </p>
            </div>
        </div>
    </div>
</div>