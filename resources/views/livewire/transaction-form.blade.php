<?php

use App\Data\TransactionData;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\TransactionService;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Spatie\LaravelData\Optional;

new class extends Component
{
    public ?Transaction $transaction = null;
    public bool $isOpen = false;
    public string $mode = 'create'; // 'create' or 'edit'

    #[Validate('required|integer|exists:accounts,id')]
    public ?int $account_id = null;

    #[Validate('required|in:income,expense,transfer')]
    public string $type = 'expense';

    #[Validate('required|numeric|min:0|max:999999.99')]
    public ?float $amount = null;

    #[Validate('required|string|max:255')]
    public string $description = '';

    #[Validate('required|date')]
    public string $transaction_date = '';

    #[Validate('nullable|integer|exists:categories,id')]
    public ?int $category_id = null;

    #[Validate('nullable|integer|exists:accounts,id')]
    public ?int $transfer_to_account_id = null;

    public bool $reconciled = false;

    // UI State
    public bool $showCategoryForm = false;
    public string $newCategoryName = '';
    public ?int $newCategoryParentId = null;
    public string $newCategoryColor = '#3b82f6';

    public function mount(?Transaction $transaction = null): void
    {
        $this->transaction = $transaction;
        $this->mode = $transaction ? 'edit' : 'create';
        $this->transaction_date = Carbon::now()->format('Y-m-d');

        if ($transaction) {
            $this->account_id = $transaction->account_id;
            $this->type = $transaction->type ?? 'expense';
            $this->amount = (float) $transaction->amount;
            $this->description = $transaction->description;
            $this->transaction_date = $transaction->transaction_date->format('Y-m-d');
            $this->category_id = $transaction->category_id;
            $this->transfer_to_account_id = $transaction->transfer_to_account_id;
            $this->reconciled = $transaction->reconciled;
        } else {
            // Set default account for new transactions
            $this->account_id = auth()->user()->accounts()->first()?->id;
        }
    }

    #[Computed]
    public function accounts()
    {
        return auth()->user()->accounts()
            ->where(function ($query) {
                // For transfers, exclude the source account from destination options
                if ($this->type === 'transfer' && $this->account_id) {
                    $query->where('id', '!=', $this->account_id);
                }
            })
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function transferAccounts()
    {
        return auth()->user()->accounts()
            ->where('id', '!=', $this->account_id)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return auth()->user()->categories()
            ->orderBy('name')
            ->get()
            ->toTree();
    }

    #[Computed]
    public function flatCategories()
    {
        return auth()->user()->categories()
            ->get()
            ->sortBy('name');
    }

    public function open(?Transaction $transaction = null): void
    {
        if ($transaction) {
            $this->mount($transaction);
        } else {
            $this->reset(['account_id', 'type', 'amount', 'description', 'category_id', 'transfer_to_account_id', 'reconciled']);
            $this->transaction_date = Carbon::now()->format('Y-m-d');
            $this->account_id = auth()->user()->accounts()->first()?->id;
        }
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->reset(['showCategoryForm', 'newCategoryName', 'newCategoryParentId', 'newCategoryColor']);
    }

    public function save(): void
    {
        $this->validate();

        // Additional validation for transfers
        if ($this->type === 'transfer') {
            $this->validate([
                'transfer_to_account_id' => 'required|integer|exists:accounts,id|different:account_id',
            ]);

            // Ensure user owns the destination account
            if (!auth()->user()->accounts()->where('id', $this->transfer_to_account_id)->exists()) {
                $this->addError('transfer_to_account_id', 'Invalid destination account.');
                return;
            }
        }

        // Ensure user owns the source account
        if (!auth()->user()->accounts()->where('id', $this->account_id)->exists()) {
            $this->addError('account_id', 'Invalid account.');
            return;
        }

        // Ensure user owns the category (if provided)
        if ($this->category_id && !auth()->user()->categories()->where('id', $this->category_id)->exists()) {
            $this->addError('category_id', 'Invalid category.');
            return;
        }

        try {
            $transactionData = new TransactionData(
                id: $this->transaction ? $this->transaction->id : Optional::create(),
                account_id: $this->account_id,
                type: $this->type,
                amount: $this->amount,
                description: $this->description,
                transaction_date: Carbon::createFromFormat('Y-m-d', $this->transaction_date),
                category_id: $this->category_id ?: Optional::create(),
                transferToAccountId: $this->transfer_to_account_id ?: Optional::create(),
                recurringPatternId: Optional::create(),
                importId: Optional::create(),
                reconciled: $this->reconciled,
                account: Optional::create(),
                category: Optional::create(),
                transferToAccount: Optional::create(),
                recurringPattern: Optional::create(),
                import: Optional::create(),
                signed_amount: Optional::create(),
                is_transfer: Optional::create(),
                is_recurring: Optional::create(),
            );

            $transactionService = app(TransactionService::class);

            if ($this->mode === 'edit' && $this->transaction) {
                $transactionService->updateTransaction($this->transaction, $transactionData);
                $this->dispatch('transaction-updated', $this->transaction->id);
            } else {
                $transaction = $transactionService->createTransaction(auth()->user(), $transactionData);
                $this->dispatch('transaction-created', $transaction->id);
            }

            $this->close();
            
            session()->flash('success', $this->mode === 'edit' ? 'Transaction updated successfully.' : 'Transaction created successfully.');

        } catch (\Exception $e) {
            $this->addError('general', 'An error occurred while saving the transaction: ' . $e->getMessage());
        }
    }

    public function delete(): void
    {
        if (!$this->transaction) {
            return;
        }

        try {
            app(TransactionService::class)->deleteTransaction($this->transaction);
            $this->dispatch('transaction-deleted', $this->transaction->id);
            $this->close();
            
            session()->flash('success', 'Transaction deleted successfully.');

        } catch (\Exception $e) {
            $this->addError('general', 'An error occurred while deleting the transaction: ' . $e->getMessage());
        }
    }

    public function showNewCategoryForm(): void
    {
        $this->showCategoryForm = true;
        $this->newCategoryName = '';
        $this->newCategoryParentId = null;
        $this->newCategoryColor = '#3b82f6';
    }

    public function createCategory(): void
    {
        $this->validate([
            'newCategoryName' => 'required|string|max:255',
            'newCategoryParentId' => 'nullable|integer|exists:categories,id',
            'newCategoryColor' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        // Ensure parent category belongs to user (if provided)
        if ($this->newCategoryParentId && !auth()->user()->categories()->where('id', $this->newCategoryParentId)->exists()) {
            $this->addError('newCategoryParentId', 'Invalid parent category.');
            return;
        }

        try {
            $category = Category::create([
                'user_id' => auth()->id(),
                'name' => $this->newCategoryName,
                'color' => $this->newCategoryColor,
                'parent_id' => $this->newCategoryParentId,
            ]);

            $this->category_id = $category->id;
            $this->showCategoryForm = false;
            $this->reset(['newCategoryName', 'newCategoryParentId', 'newCategoryColor']);

        } catch (\Exception $e) {
            $this->addError('newCategoryName', 'An error occurred while creating the category: ' . $e->getMessage());
        }
    }

    public function updatedType(): void
    {
        // Clear transfer account when type changes away from transfer
        if ($this->type !== 'transfer') {
            $this->transfer_to_account_id = null;
        }
    }

    public function updatedAccountId(): void
    {
        // Clear transfer account if it's the same as source account
        if ($this->transfer_to_account_id === $this->account_id) {
            $this->transfer_to_account_id = null;
        }
    }

    // Event listeners
    protected function getListeners(): array
    {
        return [
            'open-transaction-form' => 'openFromEvent',
            'open-transaction-form-for-date' => 'openForDate',
        ];
    }

    public function openFromEvent(?int $transactionId = null): void
    {
        if ($transactionId) {
            $transaction = Transaction::where('id', $transactionId)
                ->whereHas('account', fn($query) => $query->where('user_id', auth()->id()))
                ->first();
            
            if ($transaction) {
                $this->open($transaction);
            }
        } else {
            $this->open();
        }
    }

    public function openForDate(string $date): void
    {
        $this->open();
        $this->transaction_date = $date;
    }
}; ?>

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
                        <flux:radio wire:model.live="type" value="income" label="Income" />
                        <flux:radio wire:model.live="type" value="expense" label="Expense" />
                        <flux:radio wire:model.live="type" value="transfer" label="Transfer" />
                    </div>
                    <flux:error name="type" />
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
                    <flux:error name="account_id" />
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
                        <flux:error name="transfer_to_account_id" />
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
                        <flux:error name="amount" />
                    </flux:field>
                </div>
                <div>
                    <flux:field>
                        <flux:label>Date</flux:label>
                        <flux:input 
                            wire:model="transaction_date" 
                            type="date"
                        />
                        <flux:error name="transaction_date" />
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
                    <flux:error name="description" />
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
                    <flux:error name="category_id" />
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
                                <flux:input wire:model="newCategoryName" placeholder="Enter category name" />
                                <flux:error name="newCategoryName" />
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
                                <flux:error name="newCategoryParentId" />
                            </flux:field>
                        </div>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>Color</flux:label>
                            <flux:input wire:model="newCategoryColor" type="color" />
                            <flux:error name="newCategoryColor" />
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
                    <flux:checkbox wire:model="reconciled" label="Mark as reconciled" />
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