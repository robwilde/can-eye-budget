<div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">Automation Rules</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Create rules to automatically categorize transactions</p>
        </div>
        <div class="flex items-center space-x-3">
            @if($this->categoryRules->isNotEmpty())
                <flux:button
                    wire:click="previewBulkApplication"
                    variant="ghost"
                    wire:loading.attr="disabled"
                    wire:target="previewBulkApplication"
                >
                    <flux:icon name="play" class="w-4 h-4 mr-2" />
                    Apply All Rules
                </flux:button>
            @endif
            <flux:button wire:click="createRule" variant="primary">
                <flux:icon name="plus" class="w-4 h-4 mr-2" />
                Add Rule
            </flux:button>
        </div>
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
                            <div class="relative"
                                 x-data="{
                                     showSuggestions: @entangle('showDescriptionSuggestions'),
                                     selectedIndex: @entangle('selectedSuggestionIndex'),
                                     suggestions: @entangle('descriptionSearchResults'),

                                     handleKeydown(event) {
                                         if (!this.showSuggestions || this.suggestions.length === 0) {
                                             return;
                                         }

                                         switch(event.key) {
                                             case 'ArrowDown':
                                                 event.preventDefault();
                                                 $wire.navigateDescriptionSuggestions('down');
                                                 break;
                                             case 'ArrowUp':
                                                 event.preventDefault();
                                                 $wire.navigateDescriptionSuggestions('up');
                                                 break;
                                             case 'Enter':
                                                 if (this.selectedIndex >= 0) {
                                                     event.preventDefault();
                                                     $wire.selectCurrentSuggestion();
                                                 }
                                                 break;
                                             case 'Escape':
                                                 event.preventDefault();
                                                 $wire.hideDescriptionSuggestions();
                                                 break;
                                         }
                                     }
                                 }"
                                 @click.outside="$wire.hideDescriptionSuggestions()"
                            >
                                <flux:input
                                    wire:model.live.debounce.300ms="value"
                                    id="value"
                                    placeholder="e.g., Starbucks, Salary, grocery"
                                    autocomplete="off"
                                    @keydown="handleKeydown"
                                    @focus="if ($wire.value.length >= 2) $wire.searchDescriptions()"
                                />

                                <!-- Suggestions Dropdown -->
                                <div x-show="showSuggestions && suggestions.length > 0"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute z-10 w-full mt-1 bg-white dark:bg-zinc-800 shadow-lg rounded-md border border-zinc-200 dark:border-zinc-700 max-h-60 overflow-auto"
                                     style="display: none;">
                                    <template x-for="(suggestion, index) in suggestions" :key="index">
                                        <div class="p-3 hover:bg-zinc-50 dark:hover:bg-zinc-700 cursor-pointer border-b border-zinc-100 dark:border-zinc-700 last:border-b-0"
                                             :class="{ 'bg-blue-50 dark:bg-blue-900/20': selectedIndex === index }"
                                             @click="$wire.selectDescriptionSuggestion(index)"
                                             @mouseenter="selectedIndex = index">
                                            <div class="flex justify-between items-center">
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate"
                                                       x-text="suggestion.description"></p>
                                                </div>
                                                <div class="ml-2 flex-shrink-0">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-zinc-100 dark:bg-zinc-600 text-zinc-800 dark:text-zinc-200">
                                                        <span x-text="suggestion.usage_count"></span>
                                                        <span class="ml-1">uses</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <flux:description>Start typing to see suggestions from your transaction history</flux:description>
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
                                    wire:click="testRule({{ $rule }})"
                                    variant="soft"
                                    size="sm"
                                    title="Test this rule"
                                >
                                    <flux:icon name="play" class="w-4 h-4" />
                                </flux:button>
                                <flux:button
                                    wire:click="editRule({{ $rule->id }})"
                                    variant="ghost"
                                    size="sm"
                                    title="Edit rule"
                                >
                                    <flux:icon name="pencil" class="w-4 h-4" />
                                </flux:button>
                                <flux:button
                                    wire:click="deleteRule({{ $rule->id }})"
                                    wire:confirm="Are you sure you want to delete this rule?"
                                    variant="danger"
                                    size="sm"
                                    title="Delete rule"
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

    <!-- Rule Testing Preview Modal -->
    <flux:modal :open="$showPreviewModal" @close="closePreviewModal">
        <flux:modal.header>
            <flux:heading size="lg">Rule Test Results</flux:heading>
        </flux:modal.header>

        <flux:modal.body class="space-y-6">
            @if($testResults)
                <!-- Summary Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $testResults->totalMatches }}</div>
                        <div class="text-sm text-blue-800 dark:text-blue-300">Total Matches</div>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $testResults->uncategorizedMatches }}</div>
                        <div class="text-sm text-green-800 dark:text-green-300">New Categorizations</div>
                    </div>
                    <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4">
                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $testResults->alreadyCategorizedMatches }}</div>
                        <div class="text-sm text-orange-800 dark:text-orange-300">Re-categorizations</div>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">${{ number_format(abs($testResults->totalAmount), 2) }}</div>
                        <div class="text-sm text-purple-800 dark:text-purple-300">Total Amount</div>
                    </div>
                </div>

                <!-- Conflicts Warning -->
                @if($testResults->hasConflicts())
                    <flux:callout variant="warning">
                        <flux:icon name="exclamation-triangle" class="w-5 h-5" />
                        This rule has {{ $testResults->conflicts->count() }} potential conflicts with other rules.
                    </flux:callout>
                @endif

                <!-- Matching Transactions -->
                @if($testResults->transactions->count() > 0)
                    <div>
                        <h4 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Matching Transactions</h4>
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @foreach($testResults->transactions as $transaction)
                                <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-3 mb-1">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                                {{ $transaction->description }}
                                            </div>
                                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ $transaction->getFormattedDate() }}
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3 text-xs text-zinc-500 dark:text-zinc-400">
                                            <span>{{ $transaction->account_name }}</span>
                                            <span>{{ $transaction->getFormattedAmount() }}</span>
                                            @if($transaction->current_category)
                                                <span class="text-orange-600 dark:text-orange-400">{{ $transaction->current_category }}</span>
                                                <flux:icon name="arrow-right" class="w-3 h-3" />
                                            @endif
                                            <span class="text-green-600 dark:text-green-400 font-medium">{{ $transaction->proposed_category }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center ml-4">
                                        <span class="text-xs font-medium px-2 py-1 rounded-full
                                            {{ $transaction->getChangeType() === 'new' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                            {{ $transaction->getChangeType() === 'change' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : '' }}
                                        ">
                                            {{ $transaction->getChangeType() === 'new' ? 'New' : 'Change' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                        <flux:icon name="exclamation-circle" class="w-12 h-12 mx-auto mb-4 text-zinc-300 dark:text-zinc-600" />
                        <p class="text-lg font-medium mb-2">No matches found</p>
                        <p>This rule doesn't match any of your recent transactions.</p>
                    </div>
                @endif
            @endif
        </flux:modal.body>

        <flux:modal.footer>
            <div class="flex justify-between items-center w-full">
                <flux:button wire:click="closePreviewModal" variant="ghost">
                    Close
                </flux:button>
                @if($testResults && $testResults->totalMatches > 0)
                    <flux:button
                        wire:click="applySpecificRulesToExisting({{ $testingRuleId }})"
                        variant="primary"
                        wire:loading.attr="disabled"
                        wire:target="applySpecificRulesToExisting"
                    >
                        <span wire:loading.remove wire:target="applySpecificRulesToExisting">Apply to {{ $testResults->totalMatches }} Transactions</span>
                        <span wire:loading wire:target="applySpecificRulesToExisting">Applying...</span>
                    </flux:button>
                @endif
            </div>
        </flux:modal.footer>
    </flux:modal>

    <!-- Bulk Application Confirmation Modal -->
    <flux:modal :open="$showConfirmBulkModal" @close="closeBulkConfirmModal">
        <flux:modal.header>
            <flux:heading size="lg">Apply All Rules</flux:heading>
        </flux:modal.header>

        <flux:modal.body class="space-y-4">
            <div>
                <p class="text-zinc-700 dark:text-zinc-300 mb-4">
                    This will apply all your automation rules to recent uncategorized transactions (up to 200).
                    This action cannot be easily undone.
                </p>

                <flux:callout variant="warning">
                    <flux:icon name="exclamation-triangle" class="w-5 h-5" />
                    <strong>Important:</strong> Rules will be applied in order of priority. Higher priority rules will override lower priority ones for the same transaction.
                </flux:callout>
            </div>
        </flux:modal.body>

        <flux:modal.footer>
            <div class="flex justify-between items-center w-full">
                <flux:button wire:click="closeBulkConfirmModal" variant="ghost">
                    Cancel
                </flux:button>
                <flux:button
                    wire:click="applyRulesToExisting"
                    variant="primary"
                    wire:loading.attr="disabled"
                    wire:target="applyRulesToExisting"
                >
                    <span wire:loading.remove wire:target="applyRulesToExisting">Apply All Rules</span>
                    <span wire:loading wire:target="applyRulesToExisting">Applying Rules...</span>
                </flux:button>
            </div>
        </flux:modal.footer>
    </flux:modal>
</div>
