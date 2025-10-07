<?php /** @noinspection PhpUnusedPrivateMethodInspection */

declare(strict_types=1);

namespace App\Livewire;

use App\Data\TransactionData;
use App\Models\Category;
use App\Models\RecurringPattern;
use App\Models\Transaction;
use App\Services\TransactionService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\LaravelData\Optional;
use Throwable;

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

    #[Validate('required|in:planned,entered')]
    public string $status = 'planned';

    // Entry mode: 'enter' for actual transactions, 'plan' for future/budgeted
    #[Validate('required|in:enter,plan')]
    public string $entryMode = 'enter';

    // Recurring pattern fields
    #[Validate('nullable|integer|min:0|max:365')]
    public ?int $recurringFrequency = 0;

    #[Validate('nullable|in:always,date')]
    public ?string $recurringDuration = 'always';

    #[Validate('nullable|date|after:transaction_date')]
    public ?string $recurringEndDate = null;

    public bool $showCategoryForm = false;

    public string $newCategoryName = '';

    public ?int $newCategoryParentId = null;

    public string $newCategoryColor = '#3b82f6';

    public string $categorySearch = '';

    private TransactionService $transactionService;

    public function boot(TransactionService $transactionService): void
    {
        $this->transactionService = $transactionService;
    }

    public function mount(?Transaction $transaction = null): void
    {
        $this->transaction = $transaction;
        $this->mode = ($transaction && $transaction->exists) ? 'edit' : 'create';
        $this->transaction_date = Carbon::now()
                                        ->format('Y-m-d');

        if ($transaction) {
            $this->account_id = $transaction->account_id;
            $this->type = $transaction->type ?? 'expense';
            $this->amount = (float) $transaction->amount;
            $this->description = $transaction->description ?? '';
            $this->transaction_date = $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : Carbon::now()->format('Y-m-d');
            $this->category_id = $transaction->category_id;
            $this->transfer_to_account_id = $transaction->transfer_to_account_id;
            $this->reconciled = (bool) ($transaction->reconciled ?? false);
            $this->status = $transaction->status ?? 'planned';
            // Always determine entry mode from date, so past planned transactions switch to Enter mode
            $this->entryMode = $this->determineEntryModeFromDate($this->transaction_date);
            // Load recurring pattern if exists
            $this->recurringFrequency = 0; // Will be populated when we add recurring pattern relationship
        } else {
            // Explicitly ensure no default account is selected for new transactions
            $this->account_id = null;
            $this->type = 'expense';
            $this->amount = null;
            $this->description = '';
            $this->category_id = null;
            $this->transfer_to_account_id = null;
            $this->reconciled = false;

            // Determine status and entry mode based on the transaction date
            $this->status = $this->determineStatusFromDate($this->transaction_date);
            $this->entryMode = $this->determineEntryModeFromDate($this->transaction_date);

            $this->recurringFrequency = 0;
            $this->recurringDuration = 'always';
            $this->recurringEndDate = null;
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

    #[Computed]
    public function filteredCategories()
    {
        $categories = auth()
            ->user()
            ->categories()
            ->get();

        // Filter by search term if provided
        if ($this->categorySearch) {
            $searchTerm = mb_strtolower(mb_trim($this->categorySearch));
            $categories = $categories->filter(function ($category) use ($searchTerm) {
                return str_contains(mb_strtolower($category->full_name), $searchTerm);
            });
        }

        // Sort by full name for better hierarchy display
        return $categories->sortBy('full_name');
    }

    #[Computed]
    public function buttonText(): string
    {
        $typeCapitalized = ucfirst($this->type);

        return match (true) {
            $this->mode === 'edit' && $this->entryMode === 'plan'    => "Update $typeCapitalized",
            $this->mode === 'edit' && $this->entryMode === 'enter'   => "Confirm $typeCapitalized",
            $this->mode === 'create' && $this->entryMode === 'plan'  => "Plan $typeCapitalized",
            $this->mode === 'create' && $this->entryMode === 'enter' => "Enter $typeCapitalized",
            default                                                  => "Save $typeCapitalized",
        };
    }

    public function open(?Transaction $transaction = null): void
    {
        if ($transaction) {
            $this->mount($transaction);
        } else {
            // Clear transaction first so hooks work correctly
            $this->transaction = null;
            $this->mode = 'create';

            $this->reset(['account_id', 'type', 'amount', 'description', 'category_id', 'transfer_to_account_id', 'reconciled', 'status', 'recurringFrequency', 'recurringDuration', 'recurringEndDate']);

            // Set transaction date
            $this->transaction_date = Carbon::now()->format('Y-m-d');

            // Explicitly set status and entry mode based on date
            $this->status = $this->determineStatusFromDate($this->transaction_date);
            $this->entryMode = $this->determineEntryModeFromDate($this->transaction_date);

            // Set remaining fields
            $this->account_id = null;
            $this->type = 'expense';
            $this->recurringFrequency = 0;
            $this->recurringDuration = 'always';
            $this->recurringEndDate = null;
        }
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->reset(['showCategoryForm', 'newCategoryName', 'newCategoryParentId', 'newCategoryColor', 'categorySearch']);
    }

    /**
     * @throws Throwable
     */
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
            $recurringPatternId = null;

            // Create recurring pattern if applicable
            if ($this->entryMode === 'plan' && $this->recurringFrequency > 0 && $this->mode === 'create') {
                $frequencyPattern = $this->convertFrequencyToPattern($this->recurringFrequency);

                $endDate = null;
                if ($this->recurringDuration === 'date' && $this->recurringEndDate) {
                    $endDate = Carbon::createFromFormat('Y-m-d', $this->recurringEndDate);
                }

                $recurringPattern = RecurringPattern::create([
                    'name'                   => $this->description ?: 'Recurring '.$this->type,
                    'type'                   => $this->type,
                    'amount'                 => $this->amount,
                    'description'            => $this->description,
                    'category_id'            => $this->category_id,
                    'account_id'             => $this->account_id,
                    'transfer_to_account_id' => $this->transfer_to_account_id,
                    'frequency'              => $frequencyPattern['frequency'],
                    'frequency_interval'     => (int) $frequencyPattern['interval'],
                    'start_date'             => Carbon::createFromFormat('Y-m-d', $this->transaction_date),
                    'end_date'               => $endDate,
                    'is_active'              => true,
                ]);

                $recurringPatternId = $recurringPattern->id;
            }

            $transactionData = new TransactionData(
                id                 : $this->transaction->id ?? Optional::create(),
                account_id         : $this->account_id,
                type               : $this->type,
                amount             : $this->amount,
                description        : $this->description,
                transaction_date   : Carbon::createFromFormat('Y-m-d', $this->transaction_date),
                category_id        : $this->category_id ?: Optional::create(),
                transferToAccountId: $this->transfer_to_account_id ?: Optional::create(),
                recurringPatternId : $recurringPatternId ?: Optional::create(),
                importId           : Optional::create(),
                reconciled         : $this->reconciled,
                status             : $this->status,
                account            : Optional::create(),
                category           : Optional::create(),
                transferToAccount  : Optional::create(),
                recurringPattern   : Optional::create(),
                import             : Optional::create(),
                signed_amount      : Optional::create(),
                is_transfer        : Optional::create(),
                is_recurring       : Optional::create(),
            );

            if ($this->mode === 'edit' && $this->transaction && $this->transaction->exists) {
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

    /**
     * @throws Throwable
     */
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

    public function selectCategory(int $categoryId): void
    {
        $this->category_id = $categoryId;
        $this->categorySearch = '';
    }

    public function clearCategory(): void
    {
        $this->category_id = null;
        $this->categorySearch = '';
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

    public function updatedTransactionDate(): void
    {
        // Update status and entry mode based on new date (only for new transactions)
        if (! $this->transaction || ! $this->transaction->exists) {
            $this->status = $this->determineStatusFromDate($this->transaction_date);
            $this->entryMode = $this->determineEntryModeFromDate($this->transaction_date);
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

        // Explicitly update entry mode and status for the new date
        // (updatedTransactionDate hook won't fire if the date is the same)
        $this->status = $this->determineStatusFromDate($this->transaction_date);
        $this->entryMode = $this->determineEntryModeFromDate($this->transaction_date);
    }

    public function render(): View
    {
        return view('livewire.transaction-form');
    }

    /**
     * Toggle between enter and plan modes
     */
    public function toggleEntryMode(string $mode): void
    {
        if (in_array($mode, ['enter', 'plan'])) {
            $this->entryMode = $mode;
            $this->syncStatusWithEntryMode();
        }
    }

    /**
     * Update status when entry mode changes
     */
    public function updatedEntryMode(): void
    {
        $this->syncStatusWithEntryMode();
    }

    /**
     * Handle recurring frequency changes
     */
    public function updatedRecurringFrequency(): void
    {
        // Reset duration and end date when frequency changes
        if ($this->recurringFrequency === 0) {
            $this->recurringDuration = 'always';
            $this->recurringEndDate = null;
        }
    }

    protected function getListeners(): array
    {
        return [
            'open-transaction-form'          => 'openFromEvent',
            'open-transaction-form-for-date' => 'openForDate',
        ];
    }

    /**
     * Determine transaction status based on date
     * Future dates = planned, today/past dates = entered
     */
    private function determineStatusFromDate(string $dateString): string
    {
        $transactionDate = Carbon::createFromFormat('Y-m-d', $dateString);
        $today = Carbon::today();

        return $transactionDate->greaterThan($today) ? 'planned' : 'entered';
    }

    /**
     * Determine entry mode based on date
     * Future dates = plan mode, today/past dates = enter mode
     */
    private function determineEntryModeFromDate(string $dateString): string
    {
        // Handle empty string case
        if (empty($dateString)) {
            return 'enter';
        }

        // Parse both dates at start of day for accurate comparison
        $transactionDate = Carbon::createFromFormat('Y-m-d', $dateString)->startOfDay();
        $today = Carbon::today()->startOfDay();

        // If the transaction date is less than or equal to today (today or past), use enter mode
        // If the transaction date is greater than today (future), use plan mode
        return $transactionDate->lte($today) ? 'enter' : 'plan';
    }

    /**
     * Determine entry mode from transaction status
     * Used when editing existing transactions
     */
    private function determineEntryModeFromStatus(string $status): string
    {
        return $status === 'entered' ? 'enter' : 'plan';
    }

    /**
     * Sync status with current entry mode
     */
    private function syncStatusWithEntryMode(): void
    {
        $this->status = $this->entryMode === 'enter' ? 'entered' : 'planned';
    }

    /**
     * Convert frequency value (days) to RecurringPattern frequency type
     */
    private function convertFrequencyToPattern(int $days): array
    {
        return match ($days) {
            0       => ['frequency' => null, 'interval' => 0],
            1       => ['frequency' => 'daily', 'interval' => 1],
            7       => ['frequency' => 'weekly', 'interval' => 1],
            14      => ['frequency' => 'weekly', 'interval' => 2],
            21      => ['frequency' => 'weekly', 'interval' => 3],
            28      => ['frequency' => 'weekly', 'interval' => 4],
            30      => ['frequency' => 'monthly', 'interval' => 1],
            45      => ['frequency' => 'monthly', 'interval' => 1.5],
            60      => ['frequency' => 'monthly', 'interval' => 2],
            91      => ['frequency' => 'monthly', 'interval' => 3],
            121     => ['frequency' => 'monthly', 'interval' => 4],
            182     => ['frequency' => 'monthly', 'interval' => 6],
            365     => ['frequency' => 'yearly', 'interval' => 1],
            default => ['frequency' => 'custom', 'interval' => $days],
        };
    }
}
