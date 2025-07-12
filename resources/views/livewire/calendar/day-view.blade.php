{{-- Day View --}}
<div class="p-6">
    <div class="space-y-4">
        @php
            $dayKey = $currentDate->format('Y-m-d');
            $dayTransactions = $this->transactions[$dayKey] ?? collect();
            $dayIncome = $dayTransactions->where('type', 'income')->sum('amount');
            $dayExpenses = $dayTransactions->whereIn('type', ['expense', 'transfer'])->sum('amount');
            $dayNet = $dayIncome - $dayExpenses;
        @endphp

        {{-- Day Summary --}}
        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Income</p>
                    <p class="text-lg font-semibold text-green-600 dark:text-green-400">
                        ${{ number_format($dayIncome, 2) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Expenses</p>
                    <p class="text-lg font-semibold text-red-600 dark:text-red-400">
                        ${{ number_format($dayExpenses, 2) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Net</p>
                    <p class="text-lg font-semibold {{ $dayNet >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $dayNet >= 0 ? '+' : '' }}${{ number_format($dayNet, 2) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Transaction List --}}
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Transactions ({{ $dayTransactions->count() }})
                </h3>
                <flux:button size="sm" icon="plus" wire:click="openTransactionFormForDate('{{ $dayKey }}')">
                    Add Transaction
                </flux:button>
            </div>

            @if($dayTransactions->isNotEmpty())
                <div class="space-y-2">
                    @foreach($dayTransactions as $transaction)
                        @php
                            $isIncome = $transaction->type === 'income';
                            $isTransfer = $transaction->type === 'transfer';
                        @endphp
                        
                        <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:shadow-sm transition-shadow cursor-pointer"
                             wire:click="openTransactionForm({{ $transaction->id }})">
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
                                        @if($isTransfer && $transaction->transferToAccount)
                                            <span>•</span>
                                            <span>→ {{ $transaction->transferToAccount->name }}</span>
                                        @endif
                                        @if($transaction->reconciled)
                                            <span>•</span>
                                            <span class="text-green-600 dark:text-green-400">✓ Reconciled</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <p class="font-semibold {{ $isIncome ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $isIncome ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $transaction->transaction_date->format('g:i A') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">No transactions for this day</p>
                    <flux:button size="sm" variant="ghost" class="mt-2" wire:click="openTransactionFormForDate('{{ $dayKey }}')">
                        Add your first transaction
                    </flux:button>
                </div>
            @endif
        </div>
    </div>
</div>