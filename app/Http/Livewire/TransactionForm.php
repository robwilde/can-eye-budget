<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Data\TransactionData;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\TransactionService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\LaravelData\Optional;

final class TransactionForm extends Component
{
    public ?Transaction $transaction = null;

    public bool $isOpen = false;

    public string $mode = 'create';

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

    public bool $showCategoryForm = false;

    public string $newCategoryName = '';

    public ?int $newCategoryParentId = null;

    public string $newCategoryColor = '#3b82f6';

    private TransactionService $transactionService;

    public function boot(TransactionService $transactionService): void
    {
        $this->transactionService = $transactionService;
    }

    public function mount(?Transaction $transaction = null): void
    {
        $this->transaction = $transaction;
        $this->mode = $transaction ? 'edit' : 'create';
        $this->transaction_date = Carbon::now()
                                        ->format('Y-m-d');

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
            $this->account_id = auth()
                ->user()
                ->accounts()
                ->first()?->id;
        }
    }

    #[Computed]
    public function accounts()
    {
        return auth()
            ->user()
            ->accounts()
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
        return auth()
            ->user()
            ->accounts()
            ->where('id', '!=', $this->account_id)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return auth()
            ->user()
            ->categories()
            ->orderBy('name')
            ->get()
            ->toTree();
    }

    #[Computed]
    public function flatCategories()
    {
        return auth()
            ->user()
            ->categories()
            ->get()
            ->sortBy('name');
    }

    public function open(?Transaction $transaction = null): void
    {
        if ($transaction) {
            $this->mount($transaction);
        } else {
            $this->reset(['account_id', 'type', 'amount', 'description', 'category_id', 'transfer_to_account_id', 'reconciled']);
            $this->transaction_date = Carbon::now()
                                            ->format('Y-m-d');
            $this->account_id = auth()
                ->user()
                ->accounts()
                ->first()?->id;
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
            if (! auth()
                ->user()
                ->accounts()
                ->where('id', $this->transfer_to_account_id)
                ->exists()) {
                $this->addError('transfer_to_account_id', 'Invalid destination account.');

                return;
            }
        }

        // Ensure user owns the source account
        if (! auth()
            ->user()
            ->accounts()
            ->where('id', $this->account_id)
            ->exists()) {
            $this->addError('account_id', 'Invalid account.');

            return;
        }

        // Ensure user owns the category (if provided)
        if ($this->category_id && ! auth()
                ->user()
                ->categories()
                ->where('id', $this->category_id)
                ->exists()) {
            $this->addError('category_id', 'Invalid category.');

            return;
        }

        try {
            $transactionData = new TransactionData(
                id                 : $this->transaction->id ?? Optional::create(),
                account_id         : $this->account_id,
                type               : $this->type,
                amount             : $this->amount,
                description        : $this->description,
                transaction_date   : Carbon::createFromFormat('Y-m-d', $this->transaction_date),
                category_id        : $this->category_id ?: Optional::create(),
                transferToAccountId: $this->transfer_to_account_id ?: Optional::create(),
                recurringPatternId : Optional::create(),
                importId           : Optional::create(),
                reconciled         : $this->reconciled,
                account            : Optional::create(),
                category           : Optional::create(),
                transferToAccount  : Optional::create(),
                recurringPattern   : Optional::create(),
                import             : Optional::create(),
                signed_amount      : Optional::create(),
                is_transfer        : Optional::create(),
                is_recurring       : Optional::create(),
            );

            if ($this->mode === 'edit' && $this->transaction) {
                $this->transactionService->updateTransaction($this->transaction, $transactionData);
                $this->dispatch('transaction-updated', $this->transaction->id);
            } else {
                $transaction = $this->transactionService->createTransaction(auth()->user(), $transactionData);
                $this->dispatch('transaction-created', $transaction->id);
            }

            $this->close();

            session()->flash('success', $this->mode === 'edit' ? 'Transaction updated successfully.' : 'Transaction created successfully.');
        } catch (Exception $e) {
            $this->addError('general', 'An error occurred while saving the transaction: '.$e->getMessage());
        }
    }

    public function delete(): void
    {
        if (! $this->transaction) {
            return;
        }

        try {
            $this->transactionService->deleteTransaction($this->transaction);
            $this->dispatch('transaction-deleted', $this->transaction->id);
            $this->close();

            session()->flash('success', 'Transaction deleted successfully.');
        } catch (Exception $e) {
            $this->addError('general', 'An error occurred while deleting the transaction: '.$e->getMessage());
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
            'newCategoryName'     => 'required|string|max:255',
            'newCategoryParentId' => 'nullable|integer|exists:categories,id',
            'newCategoryColor'    => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        // Ensure parent category belongs to user (if provided)
        if ($this->newCategoryParentId && ! auth()
                ->user()
                ->categories()
                ->where('id', $this->newCategoryParentId)
                ->exists()) {
            $this->addError('newCategoryParentId', 'Invalid parent category.');

            return;
        }

        try {
            $category = Category::create([
                'user_id'   => auth()->id(),
                'name'      => $this->newCategoryName,
                'color'     => $this->newCategoryColor,
                'parent_id' => $this->newCategoryParentId,
            ]);

            $this->category_id = $category->id;
            $this->showCategoryForm = false;
            $this->reset(['newCategoryName', 'newCategoryParentId', 'newCategoryColor']);
        } catch (Exception $e) {
            $this->addError('newCategoryName', 'An error occurred while creating the category: '.$e->getMessage());
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

    public function openFromEvent(?int $transactionId = null): void
    {
        if ($transactionId) {
            $transaction = Transaction::where('id', $transactionId)
                                      ->whereHas('account', fn ($query) => $query->where('user_id', auth()->id()))
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

    public function render(): View
    {
        return view('livewire.transaction-form');
    }

    protected function getListeners(): array
    {
        return [
            'open-transaction-form'          => 'openFromEvent',
            'open-transaction-form-for-date' => 'openForDate',
        ];
    }
}
