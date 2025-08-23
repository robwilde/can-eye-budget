<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
            {{ $account ? 'Edit Account' : 'Add Account' }}
        </h2>
        <flux:button variant="ghost" wire:click="cancel" icon="x-mark" />
    </div>

    <form wire:submit="save" class="space-y-4">
        <!-- Name Field -->
        <div>
            <flux:field>
                <flux:label>Name *</flux:label>
                <flux:input wire:model="name" placeholder="Enter account name" />
                <flux:error name="name" />
            </flux:field>
        </div>

        <!-- Account Type -->
        <div>
            <flux:field>
                <flux:label>Account Type *</flux:label>
                <flux:select wire:model.live="type" placeholder="Select account type">
                    <option value="checking">Checking</option>
                    <option value="savings">Savings</option>
                    <option value="credit">Credit Card</option>
                </flux:select>
                <flux:error name="type" />
            </flux:field>
        </div>

        <!-- Balance Field -->
        <div>
            <flux:field>
                <flux:label>Initial Balance *</flux:label>
                <flux:input 
                    type="number" 
                    step="0.01" 
                    wire:model="initialBalance" 
                    placeholder="0.00" 
                />
                <flux:description>
                    @if($type === 'credit')
                        Balance should be negative for credit cards (e.g., -1500.00 for $1,500 owed)
                    @else
                        Enter the current balance of this account
                    @endif
                </flux:description>
                <flux:error name="initialBalance" />
            </flux:field>
        </div>

        <!-- Credit Limit (conditional) -->
        @if($type === 'credit')
            <div>
                <flux:field>
                    <flux:checkbox wire:model.live="hasCreditLimit">
                        Has Credit Limit
                    </flux:checkbox>
                </flux:field>
            </div>

            @if($hasCreditLimit)
                <div>
                    <flux:field>
                        <flux:label>Credit Limit</flux:label>
                        <flux:input 
                            type="number" 
                            step="0.01" 
                            wire:model="creditLimit" 
                            placeholder="5000.00" 
                        />
                        <flux:description>
                            Maximum amount you can spend on this credit card
                        </flux:description>
                        <flux:error name="creditLimit" />
                    </flux:field>
                </div>
            @endif
        @endif

        <!-- Currency -->
        <div>
            <flux:field>
                <flux:label>Currency</flux:label>
                <flux:select wire:model="currency">
                    <option value="USD">USD - US Dollar</option>
                    <option value="EUR">EUR - Euro</option>
                    <option value="GBP">GBP - British Pound</option>
                    <option value="CAD">CAD - Canadian Dollar</option>
                    <option value="AUD">AUD - Australian Dollar</option>
                </flux:select>
                <flux:error name="currency" />
            </flux:field>
        </div>

        <!-- Category -->
        <div>
            <flux:field>
                <flux:label>Category</flux:label>
                <flux:select wire:model="accountCategoryId" placeholder="Select a category (optional)">
                    <option value="">No Category</option>
                    @foreach($this->availableCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </flux:select>
                <flux:description>
                    Categories help organize accounts on the accounts page
                </flux:description>
                <flux:error name="accountCategoryId" />
            </flux:field>
        </div>

        <!-- Description -->
        <div>
            <flux:field>
                <flux:label>Description</flux:label>
                <flux:textarea wire:model="description" placeholder="Optional description..." rows="3" />
                <flux:error name="description" />
            </flux:field>
        </div>

        <!-- Visibility -->
        <div>
            <flux:field>
                <flux:checkbox wire:model="isVisibleInTotals">
                    Include in total balance calculations
                </flux:checkbox>
                <flux:description>
                    Uncheck this if you want to hide this account from total balance displays
                </flux:description>
            </flux:field>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3 pt-4">
            <flux:button variant="ghost" wire:click="cancel">
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary">
                {{ $account ? 'Update Account' : 'Create Account' }}
            </flux:button>
        </div>
    </form>
</div>