<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
            {{ $category ? 'Edit Account Category' : 'Add Account Category' }}
        </h2>
        <flux:button variant="ghost" wire:click="cancel" icon="x-mark" />
    </div>

    <form wire:submit="save" class="space-y-4">
        <!-- Name Field -->
        <div>
            <flux:field>
                <flux:label>Name *</flux:label>
                <flux:input wire:model="name" placeholder="Enter category name" />
                <flux:error name="name" />
            </flux:field>
        </div>

        <!-- Display in List -->
        <div>
            <flux:field>
                <flux:checkbox wire:model="displayInList">
                    Display category in accounts list
                </flux:checkbox>
                <flux:description>
                    When checked, accounts in this category will be grouped together on the accounts page
                </flux:description>
            </flux:field>
        </div>

        <!-- Sort Order -->
        <div>
            <flux:field>
                <flux:label>Sort Order</flux:label>
                <flux:input 
                    type="number" 
                    wire:model="sortOrder" 
                    placeholder="0" 
                    min="0"
                />
                <flux:description>
                    Lower numbers appear first. Use this to control the order categories appear on the accounts page
                </flux:description>
                <flux:error name="sortOrder" />
            </flux:field>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between pt-4">
            <div>
                @if($category)
                    <flux:button 
                        variant="danger" 
                        wire:click="delete" 
                        onclick="return confirm('Are you sure you want to delete this category? This action cannot be undone.')"
                    >
                        Delete Category
                    </flux:button>
                @endif
            </div>
            
            <div class="flex space-x-3">
                <flux:button variant="ghost" wire:click="cancel">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $category ? 'Update Category' : 'Create Category' }}
                </flux:button>
            </div>
        </div>
    </form>
</div>