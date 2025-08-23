<div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">Transaction Categories</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Organize your transactions with hierarchical categories</p>
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

    <!-- Add Category Form -->
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Add New Category</h2>
        
        <form wire:submit="addCategory" class="space-y-4">
            <div class="flex space-x-4">
                <div class="flex-1">
                    <flux:field>
                        <flux:input 
                            wire:model.live="newCategoryName" 
                            placeholder="Enter category name (e.g., Food/Groceries)" 
                        />
                        <flux:description>
                            Use forward slashes (/) to create subcategories. For example: "Food/Groceries" or "Bills/Utilities/Electricity"
                        </flux:description>
                        <flux:error name="newCategoryName" />
                    </flux:field>
                </div>
                <flux:button type="submit" variant="primary">
                    Add Category
                </flux:button>
            </div>

            <!-- Live suggestions -->
            @if($this->matchingSuggestions->isNotEmpty())
                <div class="mt-2">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-2">Existing similar categories:</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($this->matchingSuggestions as $suggestion)
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200">
                                {{ $suggestion->getFullNameAttribute() }}
                                <span class="ml-1 text-zinc-500">({{ $suggestion->transactions_count }})</span>
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </form>
    </div>

    <!-- Search -->
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-4">
        <flux:input 
            wire:model.live.debounce.300ms="searchTerm" 
            placeholder="Search categories..." 
            icon="magnifying-glass"
        />
    </div>

    <!-- Categories List -->
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
        <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Categories</h3>
        </div>

        <div class="p-6">
            @if($this->categories->isEmpty())
                <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                    <p>No categories found.</p>
                    @if($searchTerm)
                        <p class="mt-2 text-sm">Try adjusting your search term.</p>
                    @else
                        <p class="mt-2 text-sm">Create your first category above to get started.</p>
                    @endif
                </div>
            @else
                <div class="space-y-2">
                    @foreach($this->categories as $category)
                        @include('partials.category-tree-item', ['category' => $category, 'level' => 0])
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>