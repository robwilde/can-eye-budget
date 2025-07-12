{{-- Year View --}}
<div class="p-6">
    @php
        $yearProjections = $this->projections;
        $yearIncome = collect($yearProjections)->sum('total_income');
        $yearExpenses = collect($yearProjections)->sum('total_expenses');
        $yearNet = $yearIncome - $yearExpenses;
    @endphp

    {{-- Year Summary --}}
    <div class="mb-6 bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
        <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-4">{{ $currentDate->year }} Summary</h3>
        <div class="grid grid-cols-3 gap-6 text-center">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Income</p>
                <p class="text-2xl font-semibold text-green-600 dark:text-green-400">
                    ${{ number_format($yearIncome, 2) }}
                </p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Expenses</p>
                <p class="text-2xl font-semibold text-red-600 dark:text-red-400">
                    ${{ number_format($yearExpenses, 2) }}
                </p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Net</p>
                <p class="text-2xl font-semibold {{ $yearNet >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ $yearNet >= 0 ? '+' : '' }}${{ number_format($yearNet, 2) }}
                </p>
            </div>
        </div>
    </div>

    {{-- Monthly Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($yearProjections as $monthData)
            @php
                $monthDate = Carbon\Carbon::createFromFormat('Y-m', $monthData['month']);
                $isCurrentMonth = $monthDate->isCurrentMonth();
                $monthIncome = $monthData['total_income'];
                $monthExpenses = $monthData['total_expenses'];
                $monthNet = $monthIncome - $monthExpenses;
            @endphp
            
            <div 
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer {{ $isCurrentMonth ? 'ring-2 ring-blue-500' : '' }}"
                wire:click="setView('month'); $set('currentDate', '{{ $monthDate->toDateString() }}')"
            >
                {{-- Month Header --}}
                <div class="text-center mb-3">
                    <h4 class="text-lg font-semibold {{ $isCurrentMonth ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white' }}">
                        {{ $monthDate->format('M') }}
                    </h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $monthDate->format('Y') }}
                    </p>
                </div>

                {{-- Month Summary --}}
                <div class="space-y-2">
                    @if($monthIncome > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Income</span>
                            <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                ${{ number_format($monthIncome, 0) }}
                            </span>
                        </div>
                    @endif
                    
                    @if($monthExpenses > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Expenses</span>
                            <span class="text-sm font-medium text-red-600 dark:text-red-400">
                                ${{ number_format($monthExpenses, 0) }}
                            </span>
                        </div>
                    @endif

                    @if($monthNet != 0)
                        <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-600">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Net</span>
                            <span class="text-sm font-semibold {{ $monthNet >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $monthNet >= 0 ? '+' : '' }}${{ number_format($monthNet, 0) }}
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Account Breakdown --}}
                @if(count($monthData['accounts']) > 0)
                    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Account Balances</p>
                        <div class="space-y-1">
                            @foreach($monthData['accounts'] as $accountId => $accountData)
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-600 dark:text-gray-400 truncate">
                                        {{ Str::limit($accountData['name'], 10) }}
                                    </span>
                                    <span class="text-xs {{ $accountData['balance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        ${{ number_format($accountData['balance'], 0) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Visual Progress Bar --}}
                @if($monthIncome > 0 || $monthExpenses > 0)
                    <div class="mt-3">
                        @php
                            $total = $monthIncome + $monthExpenses;
                            $incomePercent = $total > 0 ? ($monthIncome / $total) * 100 : 0;
                            $expensePercent = $total > 0 ? ($monthExpenses / $total) * 100 : 0;
                        @endphp
                        
                        <div class="flex h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            @if($incomePercent > 0)
                                <div 
                                    class="bg-green-500" 
                                    style="width: {{ $incomePercent }}%"
                                    title="Income: ${{ number_format($monthIncome, 2) }}"
                                ></div>
                            @endif
                            @if($expensePercent > 0)
                                <div 
                                    class="bg-red-500" 
                                    style="width: {{ $expensePercent }}%"
                                    title="Expenses: ${{ number_format($monthExpenses, 2) }}"
                                ></div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Year Trends Chart Placeholder --}}
    <div class="mt-8 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Monthly Trends</h3>
        <div class="h-64 flex items-center justify-center bg-gray-50 dark:bg-gray-900 rounded-lg">
            <div class="text-center">
                <p class="text-gray-500 dark:text-gray-400 mb-2">Chart visualization will be added here</p>
                <p class="text-sm text-gray-400">Monthly income vs expenses trend line</p>
            </div>
        </div>
    </div>
</div>