<div>
    {{-- Flux UI Modal --}}
    <flux:modal name="transaction-form" :closable="false" wire:model="isOpen">
        <div>
                    {{-- Header with Type Dropdown and Date --}}
                    <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-3">
                            {{-- Transaction Type Dropdown --}}
                            <div class="relative">
                                <select wire:model.live="type" class="appearance-none bg-transparent text-lg font-medium capitalize pr-8 focus:outline-none cursor-pointer @if($type === 'income') text-green-600 @elseif($type === 'transfer') text-yellow-600 @else text-red-600 @endif">
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
                            <button type="button" onclick="document.querySelector('input[type=date][wire\\:model\\.live=transaction_date]').showPicker()" class="text-orange-500 font-medium cursor-pointer hover:text-orange-600">
                                {{ \Carbon\Carbon::parse($transaction_date)->format('M j, Y') }}
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
                    <div class="mb-6">
                        <div class="flex items-center justify-center mb-4">
                            <span class="text-green-500 text-sm mr-2">Enter vs Plan</span>
                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <circle cx="10" cy="10" r="3"/>
                            </svg>
                        </div>
                        <div class="flex rounded-lg border-2 border-gray-200">
                            <button 
                                type="button"
                                wire:click="$set('status', 'entered')"
                                class="flex-1 px-6 py-3 text-center transition-colors @if($status === 'entered') bg-gray-800 text-white border-r @else text-gray-700 hover:bg-gray-50 @endif"
                            >
                                <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Enter
                            </button>
                            <button 
                                type="button"
                                wire:click="$set('status', 'planned')"
                                class="flex-1 px-6 py-3 text-center transition-colors @if($status === 'planned') bg-gray-800 text-white @else text-gray-700 hover:bg-gray-50 @endif"
                            >
                                <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.379-8.379-2.828-2.828z"/>
                                </svg>
                                Plan
                            </button>
                        </div>
                        @error('status') 
                            <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> 
                        @enderror
                    </div>

                    {{-- Amount Description --}}
                    <div class="mb-6">
                        <label class="block text-gray-600 text-sm mb-2">
                            @if($status === 'planned') Planned @else Actual @endif amount with description:
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

                    {{-- Account Selection --}}
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-gray-600 text-sm mb-2">
                                Account:
                                <svg class="w-4 h-4 text-gray-400 inline ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <circle cx="10" cy="10" r="3"/>
                                </svg>
                            </label>
                            <select wire:model.live="account_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
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
                                <label class="block text-gray-600 text-sm mb-2">
                                    To Account:
                                </label>
                                <select wire:model.live="transfer_to_account_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
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

                    {{-- Recurring Options (only for planned transactions) --}}
                    @if($status === 'planned')
                        <div class="mb-6">
                            <select wire:model.live="recurring_frequency" class="w-full border border-gray-300 rounded px-3 py-2 text-sm mb-3">
                                @foreach($this->availableFrequencies as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            
                            @if($this->showRecurringOptions)
                                <div class="grid grid-cols-2 gap-3">
                                    <select wire:model.live="recurring_duration" class="border border-gray-300 rounded px-3 py-2 text-sm">
                                        @foreach($this->frequencyDurationOptions as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    
                                    @if($recurring_duration === 'until-date')
                                        <input 
                                            type="date" 
                                            wire:model="recurring_end_date"
                                            class="border border-gray-300 rounded px-3 py-2 text-sm"
                                            placeholder="2025-09-24"
                                        >
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Category Selection --}}
                    <div class="mb-6">
                        <input 
                            type="text"
                            placeholder="Category / Subcategory"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                            readonly
                        >
                        <select wire:model="category_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm mt-2">
                            <option value="">No Category</option>
                            @foreach($this->flatCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
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
                            class="w-full bg-red-600 text-white px-8 py-3 rounded-lg font-medium hover:bg-red-700 transition-colors @if($type === 'income') bg-green-600 hover:bg-green-700 @elseif($status === 'planned') bg-red-600 hover:bg-red-700 @endif"
                        >
                            @if($mode === 'edit')
                                Update {{ ucfirst($type) }}
                            @else
                                @if($status === 'planned')
                                    Plan {{ ucfirst($type) }}
                                @else
                                    Enter {{ ucfirst($type) }}
                                @endif
                            @endif
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