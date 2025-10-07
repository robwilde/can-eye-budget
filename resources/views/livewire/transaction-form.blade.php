@php use Carbon\Carbon; @endphp
<div>
    {{-- Flux UI Modal --}}
    <flux:modal name="transaction-form" :closable="false" wire:model="isOpen">
        <div>
            {{-- Header with Type Dropdown and Date --}}
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200 dark:border-gray-600">
                <div class="flex items-center gap-3">
                    {{-- Transaction Type Dropdown --}}
                    <div class="relative">
                        <select wire:model.live="type"
                                class="appearance-none bg-transparent text-lg font-medium capitalize pr-8 focus:outline-none cursor-pointer @if($type === 'income') text-green-600 @elseif($type === 'transfer') text-yellow-600 @else text-red-600 @endif">
                            <option value="expense" class="text-red-600">Expense</option>
                            <option value="income" class="text-green-600">Income</option>
                            <option value="transfer" class="text-yellow-600">Transfer</option>
                        </select>
                        <div class="absolute right-0 top-1/2 transform -translate-y-1/2 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                    @error('type')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Date Display and Close Button --}}
                <div class="flex items-center gap-4">
                    <button type="button" onclick="document.querySelector('input[type=date][wire\\:model\\.live=transaction_date]').showPicker()"
                            class="text-orange-500 font-medium cursor-pointer hover:text-orange-600">
                        {{ Carbon::parse($transaction_date)->format('M j, Y') }}
                    </button>
                    <input type="date" wire:model.live="transaction_date" class="sr-only">
                    <button wire:click="close" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Error Messages --}}
            @if($errors->has('general'))
                <div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-600 dark:text-red-300 px-4 py-3 rounded mb-4">
                    {{ $errors->first('general') }}
                </div>
            @endif

            {{-- Enter vs Plan Toggle --}}
            <div class="mb-6 text-center border-b border-gray-200 dark:border-gray-600">
                <div class="flex justify-center items-center gap-2 pb-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Enter vs Plan</span>
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.5" fill="none"/>
                        <text x="10" y="14" text-anchor="middle" font-size="12" fill="currentColor">?</text>
                    </svg>
                </div>
                <div class="flex justify-center gap-8 pb-3">
                    <button
                        type="button"
                        wire:click="toggleEntryMode('enter')"
                        class="flex items-center gap-2 text-base font-medium transition-colors {{ $entryMode === 'enter' ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300' }}"
                    >
                        @if($entryMode === 'enter')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                        Enter
                    </button>
                    <button
                        type="button"
                        wire:click="toggleEntryMode('plan')"
                        class="flex items-center gap-2 text-base font-medium transition-colors {{ $entryMode === 'plan' ? 'text-red-600 dark:text-red-400' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300' }}"
                    >
                        @if($entryMode === 'plan')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        @endif
                        Plan
                    </button>
                </div>
            </div>

            {{-- Amount Description --}}
            <div class="mb-6">
                <label class="block text-gray-600 dark:text-gray-400 text-sm mb-2">
                    {{ $entryMode === 'enter' ? 'Actual amount with description:' : 'Planned amount with description:' }}
                    <svg class="w-4 h-4 text-gray-400 inline ml-1" fill="currentColor" viewBox="0 0 20 20">
                        <circle cx="10" cy="10" r="3"/>
                    </svg>
                </label>
                <textarea
                    wire:model="description"
                    placeholder="4 * 15 zoo tickets&#10;(100 in parentheses is ignored)"
                    rows="3"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm"
                ></textarea>
                @error('description')
                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                @enderror

                <div class="mt-2 text-center text-2xl font-medium text-gray-700">
                    $<input
                        wire:model="amount"
                        type="number"
                        step="0.01"
                        min="0"
                        max="999999.99"
                        class="inline-block bg-transparent border-none text-center text-2xl font-medium w-20 focus:outline-none focus:ring-0"
                        placeholder="0"
                    /> — Australian Dollar
                </div>
                @error('amount')
                <span class="text-red-500 text-sm mt-1 block text-center">{{ $message }}</span>
                @enderror
            </div>

            {{-- Account and Recurring Pattern Selection --}}
            <div class="mb-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-600 dark:text-gray-400 text-sm mb-2">
                            Account:
                            <svg class="w-4 h-4 text-gray-400 inline ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <circle cx="10" cy="10" r="3"/>
                            </svg>
                        </label>
                        <select wire:model.live="account_id" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">Select account</option>
                            @foreach(auth()->user()->accounts as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->name }} (${{ number_format($account->getCurrentBalance(), 2) }})
                                </option>
                            @endforeach
                        </select>
                        @error('account_id')
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    @if($type === 'transfer')
                        <div>
                            <label class="block text-gray-600 dark:text-gray-400 text-sm mb-2">
                                To Account:
                            </label>
                            <select wire:model.live="transfer_to_account_id" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">Select destination</option>
                                @foreach($this->transferAccounts as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->name }} (${{ number_format($account->getCurrentBalance(), 2) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('transfer_to_account_id')
                            <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif
                </div>

                {{-- Recurring Pattern (Plan Mode Only) --}}
                @if($entryMode === 'plan')
                    <div class="mt-4">
                        <label class="block text-gray-600 dark:text-gray-400 text-sm mb-2">
                            Repeat:
                        </label>
                        <select wire:model.live="recurringFrequency" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="0">Don't repeat</option>
                            <optgroup label="───">
                                <option value="1">Everyday</option>
                                <option value="7">Every week</option>
                                <option value="30">Every month</option>
                                <option value="91">Every 3 months</option>
                                <option value="182">Every 6 months</option>
                                <option value="365">Every year</option>
                            </optgroup>
                            <optgroup label="───">
                                <option value="8">Every workday</option>
                                <option value="9">Every weekend</option>
                                <option value="10">2 on 2 off</option>
                                <option value="2">Every 2 days</option>
                                <option value="3">Every 3 days</option>
                                <option value="4">Every 4 days</option>
                                <option value="5">Every 5 days</option>
                                <option value="6">Every 6 days</option>
                            </optgroup>
                            <optgroup label="───">
                                <option value="14">Every 2 weeks</option>
                                <option value="21">Every 3 weeks</option>
                                <option value="28">Every 4 weeks</option>
                                <option value="45">Every 1.5 months</option>
                                <option value="60">Every 2 months</option>
                                <option value="121">Every 4 months</option>
                            </optgroup>
                        </select>
                        @error('recurringFrequency')
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    @if($recurringFrequency > 0)
                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-600 dark:text-gray-400 text-sm mb-2">
                                    Duration:
                                </label>
                                <select wire:model.live="recurringDuration" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="always">Always</option>
                                    <option value="date">Until date</option>
                                </select>
                            </div>

                            @if($recurringDuration === 'date')
                                <div>
                                    <label class="block text-gray-600 dark:text-gray-400 text-sm mb-2">
                                        End date:
                                    </label>
                                    <input
                                        type="date"
                                        wire:model.live="recurringEndDate"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                    >
                                    @error('recurringEndDate')
                                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            </div>

            {{-- Category Selection --}}
            <div class="mb-6">
                <label class="block text-gray-600 dark:text-gray-400 text-sm mb-2">
                    Category / Subcategory:
                </label>

                {{-- Search Input --}}
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live="categorySearch"
                        placeholder="Type to search categories..."
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 pr-10 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    @if($category_id)
                        <button
                            type="button"
                            wire:click="clearCategory"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>

                {{-- Selected Category Display --}}
                @if($category_id && !$categorySearch)
                    @php
                        $selectedCategory = $this->flatCategories->where('id', $category_id)->first();
                    @endphp
                    @if($selectedCategory)
                        <div class="mt-2 text-sm text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded-lg">
                            Selected: {{ str_replace(' > ', ' / ', $selectedCategory->full_name) }}
                        </div>
                    @endif
                @endif

                {{-- Search Results --}}
                @if($categorySearch)
                    <div class="mt-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 max-h-60 overflow-y-auto">
                        @forelse($this->filteredCategories as $category)
                            <div
                                wire:click="selectCategory({{ $category->id }})"
                                class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer border-b border-gray-100 dark:border-gray-600 last:border-b-0 {{ $category_id === $category->id ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : '' }}"
                            >
                                {{ str_replace(' > ', ' / ', $category->full_name) }}
                            </div>
                        @empty
                            <div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400 italic">
                                No categories found matching "{{ $categorySearch }}"
                            </div>
                        @endforelse
                    </div>
                @endif
                @if(!$showCategoryForm)
                    <button
                        type="button"
                        wire:click="showNewCategoryForm"
                        class="text-blue-600 text-sm hover:text-blue-700 mt-2"
                    >
                        New Category
                    </button>
                @endif

                {{-- New Category Form --}}
                @if($showCategoryForm)
                    <div class="mt-3 p-3 border rounded bg-gray-50">
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <input
                                wire:model="newCategoryName"
                                type="text"
                                placeholder="Category name"
                                class="border border-gray-300 rounded px-3 py-2 text-sm"
                            >
                            <input
                                wire:model="newCategoryColor"
                                type="color"
                                class="border border-gray-300 rounded h-10"
                            >
                        </div>
                        <select wire:model="newCategoryParentId" class="w-full border border-gray-300 rounded px-3 py-2 text-sm mb-3">
                            <option value="">No Parent</option>
                            @foreach($this->flatCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                wire:click="createCategory"
                                class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700"
                            >
                                Create
                            </button>
                            <button
                                type="button"
                                wire:click="$set('showCategoryForm', false)"
                                class="bg-gray-300 text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-400"
                            >
                                Cancel
                            </button>
                        </div>
                        @error('newCategoryName')
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
            </div>

            {{-- Action Button --}}
            <div class="flex justify-center">
                <button
                    wire:click="save"
                    class="w-full text-white px-8 py-3 rounded-lg font-medium transition-colors @if($type === 'income') bg-green-600 hover:bg-green-700 @elseif($type === 'transfer') bg-yellow-600 hover:bg-yellow-700 @else bg-red-600 hover:bg-red-700 @endif"
                >
                    {{ $this->buttonText }}
                </button>
            </div>

            {{-- Delete Button for Edit Mode --}}
            @if($mode === 'edit')
                <div class="flex justify-center mt-3">
                    <button
                        wire:click="delete"
                        wire:confirm="Are you sure you want to delete this transaction?"
                        class="text-red-600 text-sm hover:text-red-700"
                    >
                        Delete Transaction
                    </button>
                </div>
            @endif
        </div>
    </flux:modal>
</div>
