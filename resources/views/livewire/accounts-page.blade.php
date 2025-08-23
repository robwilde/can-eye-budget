<div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">Accounts</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Manage your financial accounts and categories</p>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-600 dark:text-green-300 px-4 py-3 rounded">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-600 dark:text-red-300 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- Forms Row: Account Form (left) and Category Form (right) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Add Account Form -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Add New Account</h2>
            
            <form wire:submit="addAccount" class="space-y-4">
                <flux:field>
                    <flux:label>Account Name</flux:label>
                    <flux:input 
                        wire:model="accountName" 
                        placeholder="Enter account name"
                        required
                    />
                    <flux:error name="accountName" />
                </flux:field>

                <flux:field>
                    <flux:label>Initial Balance</flux:label>
                    <flux:input 
                        type="number" 
                        step="0.01"
                        wire:model="initialBalance" 
                        placeholder="0.00"
                        required
                    />
                    @if($hasCreditLimit)
                        <flux:description class="text-amber-600 dark:text-amber-400">
                            Balance should be negative for Credit Cards
                        </flux:description>
                    @endif
                    <flux:error name="initialBalance" />
                </flux:field>

                <flux:field>
                    <flux:checkbox wire:model.live="hasCreditLimit" label="Credit Limit" />
                    <flux:description>Check if this is a credit card account</flux:description>
                </flux:field>

                @if($hasCreditLimit)
                    <flux:field>
                        <flux:label>Credit Limit Amount</flux:label>
                        <flux:input 
                            type="number" 
                            step="0.01"
                            wire:model="creditLimit" 
                            placeholder="0.00"
                            required
                        />
                        <flux:description>Maximum credit limit for this card</flux:description>
                        <flux:error name="creditLimit" />
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label>Category</flux:label>
                    <flux:select wire:model="accountCategoryId">
                        <option value="">No Category</option>
                        @foreach($this->availableCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:description>Choose a category to group this account</flux:description>
                    <flux:error name="accountCategoryId" />
                </flux:field>

                <flux:button type="submit" variant="primary" class="w-full">
                    Add Account
                </flux:button>
            </form>
        </div>

        <!-- Add Account Category Form -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Add Account Category</h2>
            
            <form wire:submit="addCategory" class="space-y-4">
                <flux:field>
                    <flux:label>Category Name</flux:label>
                    <flux:input 
                        wire:model="categoryName" 
                        placeholder="Enter category name (e.g., Bank Accounts, Credit Cards)"
                        required
                    />
                    <flux:error name="categoryName" />
                </flux:field>

                <flux:field>
                    <flux:checkbox wire:model="displayInList" label="Display Category in Accounts List" />
                    <flux:description>When enabled, accounts in this category will be grouped together on this page</flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>Sort Order</flux:label>
                    <flux:input 
                        type="number"
                        wire:model="sortOrder" 
                        placeholder="0"
                        min="0"
                    />
                    <flux:description>Lower numbers appear first (0 = highest priority)</flux:description>
                </flux:field>

                <flux:button type="submit" variant="primary" class="w-full">
                    Add Category
                </flux:button>
            </form>
        </div>
    </div>

    <!-- Total Balance -->
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total Balance</h2>
            <div class="text-2xl font-bold {{ $this->totalBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                ${{ number_format(abs($this->totalBalance), 2) }}
                @if($this->totalBalance < 0)
                    <span class="text-sm font-normal text-zinc-500">(negative)</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Accounts by Category -->
    @foreach($this->accountCategories as $category)
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <!-- Category Header -->
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $category->name }}</h3>
                    <div class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        Total: ${{ number_format($category->accounts->sum(fn($account) => $account->getCurrentBalance()), 2) }}
                    </div>
                </div>
            </div>

            <!-- Accounts in Category -->
            <div class="p-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @forelse($category->accounts as $account)
                    <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $account->name }}</h4>
                        </div>
                        
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ ucfirst($account->type) }}</span>
                            <span class="text-sm font-medium {{ $account->getCurrentBalance() >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ${{ number_format($account->getCurrentBalance(), 2) }}
                            </span>
                        </div>

                        @if($account->isCreditAccount() && $account->credit_limit)
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                Available Credit: ${{ number_format($account->getAvailableCredit(), 2) }}
                            </div>
                        @endif

                        @if($account->description)
                            <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ Str::limit($account->description, 50) }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full text-center py-8 text-zinc-500 dark:text-zinc-400">
                        No accounts in this category yet.
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach

    <!-- Uncategorized Accounts -->
    @if($this->uncategorizedAccounts->isNotEmpty())
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Uncategorized Accounts</h3>
            </div>

            <div class="p-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach($this->uncategorizedAccounts as $account)
                    <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $account->name }}</h4>
                        </div>
                        
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ ucfirst($account->type) }}</span>
                            <span class="text-sm font-medium {{ $account->getCurrentBalance() >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ${{ number_format($account->getCurrentBalance(), 2) }}
                            </span>
                        </div>

                        @if($account->isCreditAccount() && $account->credit_limit)
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                Available Credit: ${{ number_format($account->getAvailableCredit(), 2) }}
                            </div>
                        @endif

                        @if($account->description)
                            <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ Str::limit($account->description, 50) }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>