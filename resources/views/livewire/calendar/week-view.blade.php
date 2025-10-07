{{-- Week View --}}
<div class="p-6">
    @php
        $startOfWeek = $currentDate->copy()->startOfWeek();
        $endOfWeek = $currentDate->copy()->endOfWeek();
        $days = [];
        
        for ($date = $startOfWeek->copy(); $date->lte($endOfWeek); $date->addDay()) {
            $days[] = $date->copy();
        }
    @endphp

    {{-- Week Summary Header --}}
    <div class="mb-6 bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
        @php
            $weekTransactions = collect();
            foreach($days as $day) {
                $dayKey = $day->format('Y-m-d');
                $weekTransactions = $weekTransactions->merge($this->transactions[$dayKey] ?? collect());
            }
            $weekIncome = $weekTransactions->where('type', 'income')->sum('amount');
            $weekExpenses = $weekTransactions->whereIn('type', ['expense', 'transfer'])->sum('amount');
            $weekNet = $weekIncome - $weekExpenses;
        @endphp
        
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Week Summary</h3>
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Income</p>
                <p class="text-lg font-semibold text-green-600 dark:text-green-400">
                    ${{ number_format($weekIncome, 2) }}
                </p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Expenses</p>
                <p class="text-lg font-semibold text-red-600 dark:text-red-400">
                    ${{ number_format($weekExpenses, 2) }}
                </p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Net</p>
                <p class="text-lg font-semibold {{ $weekNet >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ $weekNet >= 0 ? '+' : '' }}${{ number_format($weekNet, 2) }}
                </p>
            </div>
        </div>
    </div>

    {{-- Week Grid --}}
    <div class="grid grid-cols-7 gap-4">
        @foreach($days as $day)
            @php
                $dayKey = $day->format('Y-m-d');
                $dayTransactions = $this->transactions[$dayKey] ?? collect();
                $isToday = $day->isToday();
                $dayIncome = $dayTransactions->where('type', 'income')->sum('amount');
                $dayExpenses = $dayTransactions->whereIn('type', ['expense', 'transfer'])->sum('amount');
                $dayNet = $dayIncome - $dayExpenses;
            @endphp
            
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 {{ $isToday ? 'ring-2 ring-blue-500' : '' }}">
                {{-- Day Header --}}
                <div class="text-center mb-3">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ $day->format('D') }}
                    </div>
                    <div class="text-lg font-semibold {{ $isToday ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white' }}">
                        {{ $day->day }}
                    </div>
                </div>

                {{-- Day Summary --}}
                @if($dayIncome > 0 || $dayExpenses > 0)
                    <div class="space-y-2 mb-3">
                        @if($dayIncome > 0)
                            <div class="text-xs text-green-600 dark:text-green-400 text-center">
                                +${{ number_format($dayIncome, 0) }}
                            </div>
                        @endif
                        @if($dayExpenses > 0)
                            <div class="text-xs text-red-600 dark:text-red-400 text-center">
                                -${{ number_format($dayExpenses, 0) }}
                            </div>
                        @endif
                        @if($dayNet != 0)
                            <div class="text-xs font-medium {{ $dayNet > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} text-center border-t border-gray-200 dark:border-gray-600 pt-2">
                                {{ $dayNet > 0 ? '+' : '' }}${{ number_format($dayNet, 0) }}
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Transaction List --}}
                <div class="space-y-1 max-h-40 overflow-y-auto">
                    @forelse($dayTransactions->take(5) as $transaction)
                        @php
                            $isPlanned = $transaction->isPlanned();
                            $typeColor = $transaction->type === 'income' ? 'green' : ($transaction->type === 'transfer' ? 'orange' : 'red');

                            // Planned: colored background with white text
                            // Entered: no background with colored text
                            if ($isPlanned) {
                                $bgClass = $typeColor === 'green' ? 'bg-green-600 dark:bg-green-700' :
                                          ($typeColor === 'orange' ? 'bg-orange-600 dark:bg-orange-700' : 'bg-red-600 dark:bg-red-700');
                                $textClass = 'text-white';
                                $borderClass = 'border-transparent';
                                $accountTextClass = 'text-white/80';
                            } else {
                                $bgClass = 'bg-gray-50 dark:bg-gray-700';
                                $textClass = 'text-gray-900 dark:text-white';
                                $borderClass = $typeColor === 'green' ? 'border-green-500' :
                                              ($typeColor === 'orange' ? 'border-orange-500' : 'border-red-500');
                                $accountTextClass = 'text-gray-500 dark:text-gray-400';
                            }
                        @endphp
                        <div class="text-xs p-2 {{ $bgClass }} rounded border-l-2 {{ $borderClass }}">
                            <div class="font-medium {{ $textClass }} truncate">
                                {{ Str::limit($transaction->description, 20) }}
                            </div>
                            <div class="{{ $accountTextClass }} flex justify-between">
                                <span>{{ $transaction->account->name }}</span>
                                @php
                                    $isTransferDestination = $transaction->type === 'transfer' && $transaction->isTransferDestination();
                                    if ($isPlanned) {
                                        $amountClass = 'text-white font-semibold';
                                    } else {
                                        $amountClass = $typeColor === 'green' ? 'text-green-600 dark:text-green-400' :
                                                      ($typeColor === 'orange' ? 'text-orange-600 dark:text-orange-400' : 'text-red-600 dark:text-red-400');
                                    }
                                @endphp
                                <span class="{{ $amountClass }}">
                                    {{ $transaction->type === 'income' || $isTransferDestination ? '+' : '-' }}${{ number_format($transaction->amount, 0) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-gray-400 text-center py-2">
                            No transactions
                        </div>
                    @endforelse
                    
                    @if($dayTransactions->count() > 5)
                        <div class="text-xs text-gray-400 text-center">
                            +{{ $dayTransactions->count() - 5 }} more
                        </div>
                    @endif
                </div>

                {{-- Quick Add Button --}}
                <div class="mt-3 text-center">
                    <flux:button 
                        size="xs" 
                        variant="ghost" 
                        icon="plus"
                        wire:click="openTransactionFormForDate('{{ $day->toDateString() }}')"
                    >
                        Add
                    </flux:button>
                </div>
            </div>
        @endforeach
    </div>
</div>