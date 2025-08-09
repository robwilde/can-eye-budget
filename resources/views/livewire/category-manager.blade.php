{{-- Clean Blade template for CategoryManager Livewire component --}}

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="lg">Category Management</flux:heading>
        <div class="flex gap-2">
            <flux:button wire:click="openCategoryForm" icon="plus" variant="primary">
                Add Category
            </flux:button>
            <flux:button wire:click="openRuleForm" icon="cog-6-tooth">
                Add Rule
            </flux:button>
        </div>
    </div>

    {{-- Categories Section --}}
    <flux:card>
        <div class="p-6">
            <flux:heading size="md" class="mb-4">Categories</flux:heading>
            
            @if($this->categories->count() > 0)
                <div class="space-y-2">
                    @foreach($this->categories as $category)
                        @include('livewire.category-manager.category-tree-item', ['category' => $category, 'level' => 0])
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-zinc-500">
                    <flux:icon.folder class="w-12 h-12 mx-auto mb-2" />
                    <p>No categories yet. Create your first category to get started.</p>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Category Rules Section --}}
    <flux:card>
        <div class="p-6">
            <flux:heading size="md" class="mb-4">Auto-Categorization Rules</flux:heading>
            
            @if($this->categoryRules->count() > 0)
                <div class="space-y-3">
                    @foreach($this->categoryRules as $rule)
                        <div class="flex items-center justify-between p-3 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    @if($rule->category->color)
                                        <div class="w-4 h-4 rounded-full" style="background-color: {{ $rule->category->color }}"></div>
                                    @endif
                                    @if($rule->category->icon)
                                        <flux:icon :name="$rule->category->icon" class="w-4 h-4" />
                                    @endif
                                    <span class="font-medium">{{ $rule->category->full_name }}</span>
                                </div>
                                <div class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                    If <strong>{{ $rule->field }}</strong> {{ str_replace('_', ' ', $rule->operator) }} "<strong>{{ $rule->value }}</strong>"
                                    <span class="ml-2 text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">
                                        Priority: {{ $rule->priority }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <flux:button 
                                    wire:click="openRuleForm({{ $rule->id }})" 
                                    size="sm" 
                                    variant="ghost" 
                                    icon="pencil"
                                >
                                    Edit
                                </flux:button>
                                <flux:button 
                                    wire:click="deleteRule({{ $rule->id }})" 
                                    wire:confirm="Are you sure you want to delete this rule?"
                                    size="sm" 
                                    variant="ghost" 
                                    icon="trash"
                                    class="text-red-600 hover:text-red-700"
                                >
                                    Delete
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-zinc-500">
                    <flux:icon.cog-6-tooth class="w-12 h-12 mx-auto mb-2" />
                    <p>No auto-categorization rules set up yet.</p>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Category Form Modal --}}
    <flux:modal :open="$showCategoryForm" @close="closeCategoryForm">
        <flux:modal.header>
            <flux:heading size="lg">
                {{ $editingCategory ? 'Edit Category' : 'Add New Category' }}
            </flux:heading>
        </flux:modal.header>

        <flux:modal.body>
            <form wire:submit="saveCategory" class="space-y-6">
                {{-- Category Name --}}
                <flux:field>
                    <flux:label>Category Name</flux:label>
                    <flux:input wire:model="categoryName" placeholder="Enter category name" />
                    <flux:error name="categoryName" />
                </flux:field>

                {{-- Parent Category --}}
                <flux:field>
                    <flux:label>Parent Category</flux:label>
                    <flux:select wire:model="parentCategoryId" placeholder="None (top level)">
                        <flux:select.option value="">None (top level)</flux:select.option>
                        @foreach($this->flatCategories as $category)
                            @if(!$editingCategory || $category->id !== $editingCategory->id)
                                <flux:select.option value="{{ $category->id }}">
                                    {{ $category->full_name }}
                                </flux:select.option>
                            @endif
                        @endforeach
                    </flux:select>
                    <flux:error name="parentCategoryId" />
                </flux:field>

                {{-- Color and Icon Row --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- Color --}}
                    <flux:field>
                        <flux:label>Color</flux:label>
                        <div class="flex items-center gap-2">
                            <input 
                                type="color" 
                                wire:model.live="categoryColor" 
                                class="w-10 h-10 border border-zinc-300 dark:border-zinc-600 rounded cursor-pointer"
                            />
                            <flux:input 
                                wire:model="categoryColor" 
                                placeholder="#000000" 
                                class="flex-1"
                            />
                        </div>
                        <flux:error name="categoryColor" />
                    </flux:field>

                    {{-- Icon --}}
                    <flux:field>
                        <flux:label>Icon</flux:label>
                        <flux:select wire:model.live="categoryIcon" placeholder="Select an icon">
                            <flux:select.option value="">No icon</flux:select.option>
                            <flux:select.option value="banknotes">💰 Money</flux:select.option>
                            <flux:select.option value="shopping-cart">🛒 Shopping</flux:select.option>
                            <flux:select.option value="home">🏠 Home</flux:select.option>
                            <flux:select.option value="heart">❤️ Health</flux:select.option>
                            <flux:select.option value="academic-cap">🎓 Education</flux:select.option>
                            <flux:select.option value="gift">🎁 Gifts</flux:select.option>
                            <flux:select.option value="briefcase">💼 Business</flux:select.option>
                            <flux:select.option value="car">🚗 Transportation</flux:select.option>
                        </flux:select>
                        <flux:error name="categoryIcon" />
                    </flux:field>
                </div>

                {{-- Preview --}}
                @if($categoryName)
                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <flux:label>Preview</flux:label>
                        <div class="flex items-center gap-3 mt-2">
                            @if($categoryColor)
                                <div class="w-4 h-4 rounded-full" style="background-color: {{ $categoryColor }}"></div>
                            @endif
                            @if($categoryIcon)
                                <flux:icon :name="$categoryIcon" class="w-4 h-4" />
                            @endif
                            <span>{{ $categoryName }}</span>
                        </div>
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex justify-end gap-2">
                    <flux:button type="button" wire:click="closeCategoryForm" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingCategory ? 'Update Category' : 'Create Category' }}
                    </flux:button>
                </div>
            </form>
        </flux:modal.body>
    </flux:modal>

    {{-- Rule Form Modal --}}
    <flux:modal :open="$showRuleForm" @close="closeRuleForm">
        <flux:modal.header>
            <flux:heading size="lg">
                {{ $editingRule ? 'Edit Rule' : 'Add New Rule' }}
            </flux:heading>
        </flux:modal.header>

        <flux:modal.body>
            <form wire:submit="saveRule" class="space-y-6">
                {{-- Target Category --}}
                <flux:field>
                    <flux:label>Target Category</flux:label>
                    <flux:select wire:model="ruleCategoryId" placeholder="Select category">
                        @foreach($this->flatCategories as $category)
                            <flux:select.option value="{{ $category->id }}">
                                {{ $category->full_name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="ruleCategoryId" />
                </flux:field>

                {{-- Field and Operator --}}
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Field</flux:label>
                        <flux:select wire:model.live="ruleField">
                            <flux:select.option value="description">Description</flux:select.option>
                            <flux:select.option value="amount">Amount</flux:select.option>
                        </flux:select>
                        <flux:error name="ruleField" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Operator</flux:label>
                        <flux:select wire:model="ruleOperator">
                            @if($ruleField === 'description')
                                <flux:select.option value="contains">Contains</flux:select.option>
                                <flux:select.option value="equals">Equals</flux:select.option>
                                <flux:select.option value="starts_with">Starts with</flux:select.option>
                                <flux:select.option value="ends_with">Ends with</flux:select.option>
                            @else
                                <flux:select.option value="equals">Equals</flux:select.option>
                                <flux:select.option value="greater_than">Greater than</flux:select.option>
                                <flux:select.option value="less_than">Less than</flux:select.option>
                            @endif
                        </flux:select>
                        <flux:error name="ruleOperator" />
                    </flux:field>
                </div>

                {{-- Value and Priority --}}
                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <flux:field>
                            <flux:label>Value</flux:label>
                            <flux:input 
                                wire:model="ruleValue" 
                                placeholder="{{ $ruleField === 'description' ? 'e.g., Starbucks, ATM, etc.' : 'e.g., 100.00' }}"
                            />
                            <flux:error name="ruleValue" />
                        </flux:field>
                    </div>
                    <flux:field>
                        <flux:label>Priority</flux:label>
                        <flux:input 
                            type="number" 
                            wire:model="rulePriority" 
                            min="1" 
                            max="999"
                            placeholder="1"
                        />
                        <flux:error name="rulePriority" />
                    </flux:field>
                </div>

                {{-- Rule Preview --}}
                @if($ruleField && $ruleOperator && $ruleValue && $ruleCategoryId)
                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <flux:label>Rule Preview</flux:label>
                        <div class="mt-2 text-sm">
                            If transaction <strong>{{ $ruleField }}</strong> {{ str_replace('_', ' ', $ruleOperator) }} "<strong>{{ $ruleValue }}</strong>", 
                            assign to <strong>{{ $this->flatCategories->firstWhere('id', $ruleCategoryId)?->full_name ?? 'Unknown Category' }}</strong>
                        </div>
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex justify-end gap-2">
                    <flux:button type="button" wire:click="closeRuleForm" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingRule ? 'Update Rule' : 'Create Rule' }}
                    </flux:button>
                </div>
            </form>
        </flux:modal.body>
    </flux:modal>
</div>