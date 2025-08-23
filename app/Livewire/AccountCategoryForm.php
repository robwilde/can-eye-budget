<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\AccountCategory;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class AccountCategoryForm extends Component
{
    public ?AccountCategory $category = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('boolean')]
    public bool $displayInList = true;

    #[Validate('integer|min:0')]
    public int $sortOrder = 0;

    public function mount(?AccountCategory $category = null): void
    {
        if ($category) {
            $this->category = $category;
            $this->name = $category->name;
            $this->displayInList = $category->display_in_list;
            $this->sortOrder = $category->sort_order;
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $query = AccountCategory::where('user_id', auth()->id())
                        ->where('name', $value);

                    if ($this->category) {
                        $query->where('id', '!=', $this->category->id);
                    }

                    if ($query->exists()) {
                        $fail('A category with this name already exists.');
                    }
                },
            ],
        ]);

        $data = [
            'name'            => $this->name,
            'display_in_list' => $this->displayInList,
            'sort_order'      => $this->sortOrder,
        ];

        if ($this->category) {
            $this->category->update($data);
            $message = 'Category updated successfully.';
        } else {
            $data['user_id'] = auth()->id();
            AccountCategory::create($data);
            $message = 'Category created successfully.';
        }

        session()->flash('message', $message);

        $this->dispatch('category-saved');
        $this->dispatch('close-modal');
        $this->reset();
    }

    public function delete(): void
    {
        if (! $this->category) {
            return;
        }

        $hasAccounts = $this->category->accounts()->exists();
        if ($hasAccounts) {
            session()->flash('error', 'Cannot delete category that has accounts assigned to it.');

            return;
        }

        $this->category->delete();

        session()->flash('message', 'Category deleted successfully.');

        $this->dispatch('category-saved');
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
        return view('livewire.account-category-form');
    }
}
