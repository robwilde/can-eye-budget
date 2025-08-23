<div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">Accounts</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Manage your financial accounts and categories</p>
        </div>
        <div class="flex space-x-3">
            <flux:button variant="outline" wire:click="showAddCategory" icon="folder-plus">
                Add Category
            </flux:button>
            <flux:button wire:click="showAddAccount" icon="plus">
                Add Account
            </flux:button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <flux:alert variant="success">
            {{ session('message') }}
        </flux:alert>
    @endif

    @if (session()->has('error'))
        <flux:alert variant="danger">
            {{ session('error') }}
        </flux:alert>
    @endif

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

    <!-- Account Categories -->
    @foreach($this->accountCategories as $category)
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
            <!-- Category Header -->
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $category->name }}</h3>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                            Total: ${{ number_format($category->accounts->sum(fn($account) => $account->getCurrentBalance()), 2) }}
                        </div>
                        <flux:button variant="ghost" size="sm" wire:click="editCategory({{ $category->id }})" icon="pencil">
                            Edit
                        </flux:button>
                    </div>
                </div>
            </div>

            <!-- Accounts in Category -->
            <div class="p-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @forelse($category->accounts as $account)
                    <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $account->name }}</h4>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item wire:click="editAccount({{ $account->id }})" icon="pencil">Edit</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
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
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item wire:click="editAccount({{ $account->id }})" icon="pencil">Edit</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
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

    <!-- Modals -->
    @if($showAccountForm)
        <flux:modal name="account-form" wire:model.live="showAccountForm" class="min-w-lg">
            <div class="p-6">
                @livewire('account-form', ['account' => $editingAccount], key('account-form-' . optional($editingAccount)->id))
            </div>
        </flux:modal>
    @endif

    @if($showCategoryForm)
        <flux:modal name="category-form" wire:model.live="showCategoryForm" class="min-w-lg">
            <div class="p-6">
                @livewire('account-category-form', ['category' => $editingCategory], key('category-form-' . optional($editingCategory)->id))
            </div>
        </flux:modal>
    @endif
</div>

@script
<script>
    $wire.on('account-saved', () => {
        $wire.$refresh();
    });

    $wire.on('category-saved', () => {
        $wire.$refresh();
    });
</script>
@endscript
