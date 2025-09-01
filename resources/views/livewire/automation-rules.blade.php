<div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">Automation Rules</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Create rules to automatically categorize transactions</p>
        </div>
        <flux:button wire:click="createRule" variant="primary">
            <flux:icon name="plus" class="w-4 h-4 mr-2" />
            Add Rule
        </flux:button>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <flux:callout variant="success">
            {{ session('message') }}
        </flux:callout>
    @endif

    @if (session()->has('error'))
        <flux:callout variant="danger">
            {{ session('error') }}
        </flux:callout>
    @endif

    <!-- Form Modal/Panel -->
    @if($showForm)
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-6">
                {{ $editingRuleId ? 'Edit Rule' : 'Create New Rule' }}
            </h2>
            
            <form wire:submit="saveRule" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Category Selection -->
                    <flux:field>
                        <flux:label for="categoryId">Category *</flux:label>
                        <flux:select wire:model="categoryId" id="categoryId">
                            <option value="">Select a category...</option>
                            @foreach($this->categories as $category)
                                @include('partials.category-option', ['category' => $category, 'level' => 0])
                            @endforeach
                        </flux:select>
                        <flux:error name="categoryId" />
                    </flux:field>

                    <!-- Account Selection -->
                    <flux:field>
                        <flux:label for="accountId">Account (Optional)</flux:label>
                        <flux:select wire:model="accountId" id="accountId">
                            <option value="">All accounts</option>
                            @foreach($this->accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:description>Leave blank to apply to all accounts</flux:description>
                    </flux:field>

                    <!-- Field Selection -->
                    <flux:field>
                        <flux:label for="field">Field to Match *</flux:label>
                        <flux:select wire:model.live="field" id="field">
                            <option value="description">Transaction Description</option>
                            <option value="amount">Transaction Amount</option>
                        </flux:select>
                        <flux:error name="field" />
                    </flux:field>

                    <!-- Operator Selection -->
                    <flux:field>
                        <flux:label for="operator">Condition *</flux:label>
                        <flux:select wire:model="operator" id="operator">
                            @foreach($this->fieldOperators as $operatorKey => $operatorLabel)
                                <option value="{{ $operatorKey }}">{{ $operatorLabel }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="operator" />
                    </flux:field>

                    <!-- Value Input -->
                    <flux:field>
                        <flux:label for="value">Value *</flux:label>
                        @if($field === 'amount')
                            <flux:input 
                                wire:model="value" 
                                id="value"
                                type="number"
                                step="0.01"
                                placeholder="e.g., 100.00"
                            />
                            <flux:description>Enter the amount to compare against</flux:description>
                        @else
                            <flux:input 
                                wire:model="value" 
                                id="value"
                                placeholder="e.g., Starbucks, AMZN, grocery"
                            />
                            <flux:description>Text to match in transaction descriptions</flux:description>
                        @endif
                        <flux:error name="value" />
                    </flux:field>

                    <!-- Priority -->
                    <flux:field>
                        <flux:label for="priority">Priority *</flux:label>
                        <flux:input 
                            wire:model="priority" 
                            id="priority"
                            type="number"
                            min="1"
                            max="100"
                        />
                        <flux:description>Higher numbers = higher priority (1-100)</flux:description>
                        <flux:error name="priority" />
                    </flux:field>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button wire:click="cancelForm" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingRuleId ? 'Update Rule' : 'Create Rule' }}
                    </flux:button>
                </div>
            </form>
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-4">
        <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
            <!-- Search -->
            <div class="flex-1">
                <flux:input 
                    wire:model.live.debounce.300ms="searchTerm" 
                    placeholder="Search rules by category, account, or value..." 
                    icon="magnifying-glass"
                />
            </div>
            
            <!-- Account Filter -->
            <div class="md:w-64">
                <flux:select wire:model.live="filterAccountId">
                    <option value="">All accounts</option>
                    @foreach($this->accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </flux:select>
            </div>
        </div>
    </div>

    <!-- Rules List -->
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
        <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                Rules ({{ $this->categoryRules->count() }})
            </h3>
        </div>

        <div class="p-6">
            @if($this->categoryRules->isEmpty())
                <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                    @if($searchTerm || $filterAccountId)
                        <flux:icon name="magnifying-glass" class="w-12 h-12 mx-auto mb-4 text-zinc-300 dark:text-zinc-600" />
                        <p class="text-lg font-medium mb-2">No matching rules found</p>
                        <p>Try adjusting your search or filter criteria.</p>
                    @else
                        <flux:icon name="cog-6-tooth" class="w-12 h-12 mx-auto mb-4 text-zinc-300 dark:text-zinc-600" />
                        <p class="text-lg font-medium mb-2">No automation rules yet</p>
                        <p>Create your first rule to automatically categorize transactions.</p>
                        <flux:button wire:click="createRule" variant="primary" class="mt-4">
                            Create First Rule
                        </flux:button>
                    @endif
                </div>
            @else
                <div class="space-y-4">
                    @foreach($this->categoryRules as $rule)
                        <div class="flex items-center justify-between p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <flux:badge variant="outline" class="text-sm">
                                        Priority {{ $rule->priority }}
                                    </flux:badge>
                                    @if($rule->account)
                                        <flux:badge variant="soft">
                                            {{ $rule->account->name }}
                                        </flux:badge>
                                    @else
                                        <flux:badge variant="ghost">
                                            All accounts
                                        </flux:badge>
                                    @endif
                                </div>
                                
                                <div class="text-sm text-zinc-900 dark:text-zinc-100 font-medium">
                                    @if($rule->category)
                                        <span class="inline-flex items-center">
                                            <flux:icon name="arrow-right" class="w-4 h-4 mr-1" />
                                            {{ $rule->category->name }}
                                        </span>
                                    @else
                                        <span class="text-red-600 dark:text-red-400">
                                            <flux:icon name="exclamation-triangle" class="w-4 h-4 inline mr-1" />
                                            Category not found
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                    When <strong>{{ $rule->field === 'description' ? 'description' : 'amount' }}</strong>
                                    <strong>{{ $this->fieldOperators()[$rule->operator] ?? $rule->operator }}</strong>
                                    <strong class="text-zinc-900 dark:text-zinc-100">"{{ $rule->value }}"</strong>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <flux:button 
                                    wire:click="editRule({{ $rule->id }})" 
                                    variant="ghost" 
                                    size="sm"
                                >
                                    <flux:icon name="pencil" class="w-4 h-4" />
                                </flux:button>
                                <flux:button 
                                    wire:click="deleteRule({{ $rule->id }})" 
                                    wire:confirm="Are you sure you want to delete this rule?"
                                    variant="danger" 
                                    size="sm"
                                >
                                    <flux:icon name="trash" class="w-4 h-4" />
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
