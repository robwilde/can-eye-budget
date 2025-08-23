<div>
    {{-- Modal --}}
    @if($isOpen)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="close"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ $mode === 'edit' ? 'Edit Transaction' : 'Add Transaction' }}
                        </h3>
                        <button wire:click="close" class="text-gray-400 hover:text-gray-600 dark:text-gray-300 dark:hover:text-gray-400">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-6">
                        @if($errors->has('general'))
                            <div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-600 dark:text-red-300 px-4 py-3 rounded">
                                {{ $errors->first('general') }}
                            </div>
                        @endif

                        {{-- Transaction Type --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Transaction Type</label>
                            <div class="flex gap-4">
                                <label class="flex items-center text-gray-700 dark:text-gray-300">
                                    <input type="radio" wire:model.live="type" value="income" class="mr-2">
                                    Income
                                </label>
                                <label class="flex items-center text-gray-700 dark:text-gray-300">
                                    <input type="radio" wire:model.live="type" value="expense" class="mr-2">
                                    Expense
                                </label>
                                <label class="flex items-center text-gray-700 dark:text-gray-300">
                                    <input type="radio" wire:model.live="type" value="transfer" class="mr-2">
                                    Transfer
                                </label>
                            </div>
                            @error('type') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Account Selection --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ $type === 'transfer' ? 'From Account' : 'Account' }}
                            </label>
                            <select wire:model.live="account_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">Select account</option>
                                @foreach(auth()->user()->accounts as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->name }} ({{ ucfirst($account->type) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('account_id') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Transfer Destination (only for transfers) --}}
                        @if($type === 'transfer')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To Account</label>
                                <select wire:model.live="transfer_to_account_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="">Select destination account</option>
                                    @foreach($this->transferAccounts as $account)
                                        <option value="{{ $account->id }}">
                                            {{ $account->name }} ({{ ucfirst($account->type) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('transfer_to_account_id') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        {{-- Amount and Date --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Amount</label>
                                <input
                                    wire:model="amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="999999.99"
                                    placeholder="0.00"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                />
                                @error('amount') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                                <input
                                    wire:model="transaction_date"
                                    type="date"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                />
                                @error('transaction_date') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                            <input
                                wire:model="description"
                                placeholder="Enter transaction description"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                            />
                            @error('description') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Category Selection --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                            <div class="flex gap-2">
                                <select wire:model="category_id" class="flex-1 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="">No Category</option>
                                    @foreach($this->flatCategories as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button
                                    wire:click="showNewCategoryForm"
                                    type="button"
                                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300"
                                >
                                    New
                                </button>
                            </div>
                            @error('category_id') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- New Category Form --}}
                        @if($showCategoryForm)
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-4">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100">Create New Category</h4>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category Name</label>
                                        <input wire:model="newCategoryName" placeholder="Enter category name" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100"/>
                                        @error('newCategoryName') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Parent Category</label>
                                        <select wire:model="newCategoryParentId" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100">
                                            <option value="">None (top level)</option>
                                            @foreach($this->flatCategories as $category)
                                                <option value="{{ $category->id }}">
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('newCategoryParentId') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Color</label>
                                    <input wire:model="newCategoryColor" type="color" class="w-20 h-10 border border-gray-300 dark:border-gray-600 rounded"/>
                                    @error('newCategoryColor') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex gap-2">
                                    <button wire:click="createCategory" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                        Create Category
                                    </button>
                                    <button wire:click="$set('showCategoryForm', false)" class="bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded hover:bg-gray-400 dark:hover:bg-gray-500">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Additional Options --}}
                        <div>
                            <label class="flex items-center text-gray-700 dark:text-gray-300">
                                <input type="checkbox" wire:model="reconciled" class="mr-2">
                                Mark as reconciled
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer Actions --}}
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="save" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ $mode === 'edit' ? 'Update' : 'Create' }} Transaction
                    </button>
                    <button wire:click="close" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                    @if($mode === 'edit')
                        <button
                            wire:click="delete"
                            wire:confirm="Are you sure you want to delete this transaction?"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-red-300 dark:border-red-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-base font-medium text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Delete
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>