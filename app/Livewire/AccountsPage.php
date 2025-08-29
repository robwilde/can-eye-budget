<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Account;
use App\Models\AccountCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class AccountsPage extends Component
{
    // Account form properties
    #[Validate('required|min:1|max:255')]
    public string $accountName = '';

    #[Validate('required|numeric')]
    public float $initialBalance = 0;

    public bool $hasCreditLimit = false;

    #[Validate('nullable|numeric|min:0')]
    public ?float $creditLimit = null;

    #[Validate('nullable|exists:account_categories,id')]
    public ?int $accountCategoryId = null;

    // Account category form properties
    #[Validate('required|min:1|max:255')]
    public string $categoryName = '';

    public bool $displayInList = true;

    public int $sortOrder = 0;

    public function addAccount(): void
    {
        // Custom validation for credit limit accounts
        if ($this->hasCreditLimit && $this->initialBalance >= 0) {
            $this->addError('initialBalance', 'Balance should be negative for Credit Cards');

            return;
        }

        $this->validate([
            'accountName'       => 'required|min:1|max:255',
            'initialBalance'    => 'required|numeric',
            'creditLimit'       => $this->hasCreditLimit ? 'required|numeric|min:0' : 'nullable',
            'accountCategoryId' => 'nullable|exists:account_categories,id',
        ]);

        Account::create([
            'user_id'             => auth()->id(),
            'name'                => $this->accountName,
            'type'                => $this->hasCreditLimit ? 'credit' : 'checking',
            'initial_balance'     => $this->initialBalance,
            'credit_limit'        => $this->hasCreditLimit ? $this->creditLimit : null,
            'account_category_id' => $this->accountCategoryId,
        ]);

        $this->resetAccountForm();
        session()->flash('message', 'Account created successfully!');
    }

    public function addCategory(): void
    {
        $this->validate([
            'categoryName' => 'required|min:1|max:255',
        ]);

        AccountCategory::create([
            'user_id'         => auth()->id(),
            'name'            => $this->categoryName,
            'display_in_list' => $this->displayInList,
            'sort_order'      => $this->sortOrder,
        ]);

        $this->resetCategoryForm();
        session()->flash('message', 'Account category created successfully!');
    }

    #[Computed]
    public function accountCategories(): Collection
    {
        return AccountCategory::forUser(auth()->id())
                              ->displayInList()
                              ->ordered()
                              ->with([
                                  'accounts' => function ($query) {
                                      $query->forUser(auth()->id())->orderBy('name');
                                  },
                              ])
                              ->get();
    }

    #[Computed]
    public function uncategorizedAccounts(): Collection
    {
        return Account::forUser(auth()->id())
                      ->whereNull('account_category_id')
                      ->orderBy('name')
                      ->get();
    }

    #[Computed]
    public function totalBalance()
    {
        return Account::forUser(auth()->id())
                      ->visibleInTotals()
                      ->get()
                      ->sum(fn (Account $account) => $account->getCurrentBalance());
    }

    #[Computed]
    public function availableCategories(): Collection
    {
        return AccountCategory::forUser(auth()->id())
                              ->ordered()
                              ->get();
    }

    public function render(): View
    {
        return view('livewire.accounts-page')
            ->layout('components.layouts.app', ['title' => 'Accounts']);
    }

    protected function resetAccountForm(): void
    {
        $this->accountName = '';
        $this->initialBalance = 0;
        $this->hasCreditLimit = false;
        $this->creditLimit = null;
        $this->accountCategoryId = null;
    }

    protected function resetCategoryForm(): void
    {
        $this->categoryName = '';
        $this->displayInList = true;
        $this->sortOrder = 0;
    }
}
