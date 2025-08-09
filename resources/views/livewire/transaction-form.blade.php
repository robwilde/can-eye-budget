<div>
    {{-- Modal --}}
    <flux:modal :open="$isOpen" @close="close" class="max-w-2xl">
        <flux:modal.header>
            <flux:heading size="lg">
                {{ $mode === 'edit' ? 'Edit Transaction' : 'Add Transaction' }}
            </flux:heading>
        </flux:modal.header>

        <flux:modal.body class="space-y-6">
            @if($errors->has('general'))
                <flux:badge color="red" size="sm">
                    {{ $errors->first('general') }}
                </flux:badge>
            @endif

            {{-- Transaction Type --}}
            <div>
                <flux:field>
                    <flux:label>Transaction Type</flux:label>
                    <div class="flex gap-2">
                        <flux:radio wire:model.live="type" value="income" label="Income"/>
                        <flux:radio wire:model.live="type" value="expense" label="Expense"/>
                        <flux:radio wire:model.live="type" value="transfer" label="Transfer"/>
                    </div>
                    <flux:error name="type"/>
                </flux:field>
            </div>

            {{-- Account Selection --}}
            <div>
                <flux:field>
                    <flux:label>
                        {{ $type === 'transfer' ? 'From Account' : 'Account' }}
                    </flux:label>
                    <flux:select wire:model.live="account_id" placeholder="Select account">
                        @foreach(auth()->user()->accounts as $account)
                            <flux:select.option value="{{ $account->id }}">
                                {{ $account->name }} ({{ ucfirst($account->type) }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="account_id"/>
                </flux:field>
            </div>

            {{-- Transfer Destination (only for transfers) --}}
            @if($type === 'transfer')
                <div>
                    <flux:field>
                        <flux:label>To Account</flux:label>
                        <flux:select wire:model.live="transfer_to_account_id" placeholder="Select destination account">
                            @foreach($this->transferAccounts as $account)
                                <flux:select.option value="{{ $account->id }}">
                                    {{ $account->name }} ({{ ucfirst($account->type) }})
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="transfer_to_account_id"/>
                    </flux:field>
                </div>
            @endif

            {{-- Amount and Description --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:field>
                        <flux:label>Amount</flux:label>
                        <flux:input
                            wire:model="amount"
                            type="number"
                            step="0.01"
                            min="0"
                            max="999999.99"
                            placeholder="0.00"
                        />
                        <flux:error name="amount"/>
                    </flux:field>
                </div>
                <div>
                    <flux:field>
                        <flux:label>Date</flux:label>
                        <flux:input
                            wire:model="transaction_date"
                            type="date"
                        />
                        <flux:error name="transaction_date"/>
                    </flux:field>
                </div>
            </div>

            {{-- Description --}}
            <div>
                <flux:field>
                    <flux:label>Description</flux:label>
                    <flux:input
                        wire:model="description"
                        placeholder="Enter transaction description"
                    />
                    <flux:error name="description"/>
                </flux:field>
            </div>

            {{-- Category Selection --}}
            <div>
                <flux:field>
                    <flux:label>Category</flux:label>
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <flux:select wire:model="category_id" placeholder="Select category (optional)">
                                <flux:select.option value="">No Category</flux:select.option>
                                @foreach($this->flatCategories as $category)
                                    <flux:select.option value="{{ $category->id }}">
                                        {{ $category->full_name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                        <flux:button
                            wire:click="showNewCategoryForm"
                            variant="ghost"
                            size="sm"
                            icon="plus"
                        >
                            New
                        </flux:button>
                    </div>
                    <flux:error name="category_id"/>
                </flux:field>
            </div>

            {{-- New Category Form --}}
            @if($showCategoryForm)
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 space-y-4">
                    <h4 class="font-medium text-gray-900 dark:text-white">Create New Category</h4>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:field>
                                <flux:label>Category Name</flux:label>
                                <flux:input wire:model="newCategoryName" placeholder="Enter category name"/>
                                <flux:error name="newCategoryName"/>
                            </flux:field>
                        </div>
                        <div>
                            <flux:field>
                                <flux:label>Parent Category</flux:label>
                                <flux:select wire:model="newCategoryParentId" placeholder="None (top level)">
                                    @foreach($this->flatCategories as $category)
                                        <flux:select.option value="{{ $category->id }}">
                                            {{ $category->name }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="newCategoryParentId"/>
                            </flux:field>
                        </div>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>Color</flux:label>
                            <flux:input wire:model="newCategoryColor" type="color"/>
                            <flux:error name="newCategoryColor"/>
                        </flux:field>
                    </div>

                    <div class="flex gap-2">
                        <flux:button wire:click="createCategory" variant="primary" size="sm">
                            Create Category
                        </flux:button>
                        <flux:button wire:click="$set('showCategoryForm', false)" variant="ghost" size="sm">
                            Cancel
                        </flux:button>
                    </div>
                </div>
            @endif

            {{-- Additional Options --}}
            <div>
                <flux:field>
                    <flux:checkbox wire:model="reconciled" label="Mark as reconciled"/>
                </flux:field>
            </div>
        </flux:modal.body>

        <flux:modal.footer>
            <div class="flex justify-between">
                <div>
                    @if($mode === 'edit')
                        <flux:button
                            wire:click="delete"
                            variant="danger"
                            wire:confirm="Are you sure you want to delete this transaction?"
                        >
                            Delete
                        </flux:button>
                    @endif
                </div>
                <div class="flex gap-2">
                    <flux:button wire:click="close" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button wire:click="save" variant="primary">
                        {{ $mode === 'edit' ? 'Update' : 'Create' }} Transaction
                    </flux:button>
                </div>
            </div>
        </flux:modal.footer>
    </flux:modal>
</div>
