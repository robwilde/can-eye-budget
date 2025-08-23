<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Account;
use App\Models\AccountCategory;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class AccountForm extends Component
{
    public ?Account $account = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|in:checking,savings,credit')]
    public string $type = 'checking';

    #[Validate('required|numeric')]
    public float $initialBalance = 0.0;

    #[Validate('nullable|numeric|min:0')]
    public ?float $creditLimit = null;

    #[Validate('nullable|string|max:3')]
    public string $currency = 'USD';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('nullable|integer|exists:account_categories,id')]
    public ?int $accountCategoryId = null;

    #[Validate('boolean')]
    public bool $isVisibleInTotals = true;

    public bool $hasCreditLimit = false;

    public function mount(?Account $account = null): void
    {
        if ($account) {
            $this->account = $account;
            $this->name = $account->name;
            $this->type = $account->type;
            $this->initialBalance = (float) $account->initial_balance;
            $this->creditLimit = $account->credit_limit ? (float) $account->credit_limit : null;
            $this->currency = $account->currency;
            $this->description = $account->description ?? '';
            $this->accountCategoryId = $account->account_category_id;
            $this->isVisibleInTotals = $account->is_visible_in_totals;
            $this->hasCreditLimit = $account->credit_limit !== null;
        }
    }

    public function updatedType(): void
    {
        if ($this->type === 'credit') {
            $this->hasCreditLimit = true;
            if ($this->initialBalance > 0) {
                $this->initialBalance = -abs($this->initialBalance);
            }
        } else {
            $this->hasCreditLimit = false;
            $this->creditLimit = null;
        }
    }

    public function updatedHasCreditLimit(): void
    {
        if (! $this->hasCreditLimit) {
            $this->creditLimit = null;
        }
    }

    #[Computed]
    public function availableCategories()
    {
        return AccountCategory::forUser(auth()->id())
            ->ordered()
            ->get();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'                 => $this->name,
            'type'                 => $this->type,
            'initial_balance'      => $this->initialBalance,
            'credit_limit'         => $this->hasCreditLimit ? $this->creditLimit : null,
            'currency'             => $this->currency,
            'description'          => $this->description,
            'account_category_id'  => $this->accountCategoryId,
            'is_visible_in_totals' => $this->isVisibleInTotals,
        ];

        if ($this->account) {
            $this->account->update($data);
            $message = 'Account updated successfully.';
        } else {
            $data['user_id'] = auth()->id();
            Account::create($data);
            $message = 'Account created successfully.';
        }

        session()->flash('message', $message);

        $this->dispatch('account-saved');
        $this->dispatch('close-modal');
        $this->reset();
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal');
        $this->reset();
    }

    public function render()
    {
        return view('livewire.account-form');
    }
}
