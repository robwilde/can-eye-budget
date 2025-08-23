<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Account;
use App\Models\AccountCategory;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class AccountsPage extends Component
{
    public bool $showAccountForm = false;

    public bool $showCategoryForm = false;

    public ?Account $editingAccount = null;

    public ?AccountCategory $editingCategory = null;

    public function showAddAccount(): void
    {
        $this->editingAccount = null;
        $this->showAccountForm = true;
        $this->dispatch('show-account-form');
    }

    public function editAccount(Account $account): void
    {
        $this->editingAccount = $account;
        $this->showAccountForm = true;
        $this->dispatch('show-account-form');
    }

    public function showAddCategory(): void
    {
        $this->editingCategory = null;
        $this->showCategoryForm = true;
        $this->dispatch('show-category-form');
    }

    public function editCategory(AccountCategory $category): void
    {
        $this->editingCategory = $category;
        $this->showCategoryForm = true;
        $this->dispatch('show-category-form');
    }

    public function closeAccountForm(): void
    {
        $this->showAccountForm = false;
        $this->editingAccount = null;
    }

    public function closeCategoryForm(): void
    {
        $this->showCategoryForm = false;
        $this->editingCategory = null;
    }

    #[Computed]
    public function accountCategories()
    {
        return AccountCategory::forUser(auth()->id())
            ->displayInList()
            ->ordered()
            ->with(['accounts' => function ($query) {
                $query->forUser(auth()->id())->orderBy('name');
            }])
            ->get();
    }

    #[Computed]
    public function uncategorizedAccounts()
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

    public function render()
    {
        return view('livewire.accounts-page')
            ->layout('components.layouts.app.sidebar');
    }
}
