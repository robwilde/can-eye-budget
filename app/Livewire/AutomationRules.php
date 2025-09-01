<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Account;
use App\Models\Category;
use App\Models\CategoryRule;
use App\Models\Transaction;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class AutomationRules extends Component
{
    // Form properties for creating/editing rules
    public ?int $editingRuleId = null;

    public ?int $categoryId = null;

    public ?int $accountId = null;

    #[Validate('required|in:description,amount')]
    public string $field = 'description';

    #[Validate('required|string')]
    public string $operator = 'contains';

    #[Validate('required|string|max:255')]
    public string $value = '';

    #[Validate('required|integer|min:1|max:100')]
    public int $priority = 10;

    // Filter properties
    public ?int $filterAccountId = null;

    public string $searchTerm = '';

    // UI state
    public bool $showForm = false;

    // Description search properties
    public array $descriptionSearchResults = [];

    public bool $showDescriptionSuggestions = false;

    public int $selectedSuggestionIndex = -1;

    public function mount(): void
    {
        // Initialize with sensible defaults
        $this->priority = 10;
        $this->field = 'description';
        $this->operator = 'contains';
    }

    public function createRule(): void
    {
        $this->showForm = true;
        $this->resetForm();
    }

    public function editRule(CategoryRule $rule): void
    {
        $this->editingRuleId = $rule->id;
        $this->categoryId = $rule->category_id;
        $this->accountId = $rule->account_id;
        $this->field = $rule->field;
        $this->operator = $rule->operator;
        $this->value = $rule->value;
        $this->priority = $rule->priority;
        $this->showForm = true;
    }

    public function saveRule(): void
    {
        $this->validate([
            'categoryId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $category = Category::forUser(auth()->id())->find($value);
                    if (! $category) {
                        $fail('The selected category is invalid.');
                    }
                },
            ],
            'accountId' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    if ($value && ! Account::where('user_id', auth()->id())->find($value)) {
                        $fail('The selected account is invalid.');
                    }
                },
            ],
            'field'    => 'required|in:description,amount',
            'operator' => 'required|string',
            'value'    => 'required|string|max:255',
            'priority' => 'required|integer|min:1|max:100',
        ]);

        try {
            $data = [
                'category_id' => $this->categoryId,
                'account_id'  => $this->accountId,
                'field'       => $this->field,
                'operator'    => $this->operator,
                'value'       => $this->value,
                'priority'    => $this->priority,
            ];

            if ($this->editingRuleId) {
                $rule = CategoryRule::findOrFail($this->editingRuleId);
                $rule->update($data);
                session()->flash('message', 'Rule updated successfully.');
            } else {
                CategoryRule::create($data);
                session()->flash('message', 'Rule created successfully.');
            }

            $this->resetForm();
            $this->showForm = false;
        } catch (Exception $e) {
            session()->flash('error', 'Error saving rule: '.$e->getMessage());
        }
    }

    public function deleteRule(CategoryRule $rule): void
    {
        try {
            $rule->delete();
            session()->flash('message', 'Rule deleted successfully.');
        } catch (Exception $e) {
            session()->flash('error', 'Error deleting rule: '.$e->getMessage());
        }
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    #[Computed]
    public function categoryRules(): Collection
    {
        $query = CategoryRule::with(['category', 'account'])
                             ->byPriority();

        if ($this->filterAccountId) {
            $query->forAccount($this->filterAccountId);
        }

        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q
                    ->where('value', 'like', '%'.$this->searchTerm.'%')
                    ->orWhereHas('category', function ($categoryQuery) {
                        $categoryQuery->where('name', 'like', '%'.$this->searchTerm.'%');
                    })
                    ->orWhereHas('account', function ($accountQuery) {
                        $accountQuery->where('name', 'like', '%'.$this->searchTerm.'%');
                    });
            });
        }

        return $query->get();
    }

    #[Computed]
    public function accounts(): Collection
    {
        return Account::where('user_id', auth()->id())
                      ->orderBy('name')
                      ->get();
    }

    #[Computed]
    public function categories(): Collection
    {
        return Category::forUser(auth()->id())
                       ->defaultOrder()
                       ->get()
                       ->toTree();
    }

    #[Computed]
    public function fieldOperators(): array
    {
        return match ($this->field) {
            'description' => [
                'contains'    => 'Contains',
                'equals'      => 'Equals',
                'starts_with' => 'Starts with',
                'ends_with'   => 'Ends with',
            ],
            'amount' => [
                'greater_than' => 'Greater than',
                'less_than'    => 'Less than',
                'equals'       => 'Equals',
            ],
            default => [],
        };
    }

    public function updatedField(): void
    {
        // Reset operator when field changes
        $operators = $this->fieldOperators();
        $this->operator = array_key_first($operators) ?? 'contains';

        // Hide suggestions when field changes
        $this->showDescriptionSuggestions = false;
        $this->selectedSuggestionIndex = -1;
    }

    public function updatedValue(): void
    {
        // Trigger search when value changes
        if ($this->field === 'description' && mb_strlen($this->value) >= 2) {
            $this->searchDescriptions();
        } else {
            $this->showDescriptionSuggestions = false;
            $this->selectedSuggestionIndex = -1;
        }
    }

    public function searchDescriptions(): void
    {
        if ($this->field !== 'description' || mb_strlen($this->value) < 2) {
            $this->descriptionSearchResults = [];
            $this->showDescriptionSuggestions = false;

            return;
        }

        $cacheKey = 'description_search:'.md5($this->value.'|'.$this->accountId);

        $this->descriptionSearchResults = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return Transaction::descriptionSearch($this->value, $this->accountId)
                              ->get()
                              ->map(function ($result) {
                                  return [
                                      'description'     => $result->description,
                                      'usage_count'     => $result->usage_count,
                                      'relevance_score' => $result->relevance_score,
                                  ];
                              })
                              ->toArray();
        });

        $this->showDescriptionSuggestions = count($this->descriptionSearchResults) > 0;
        $this->selectedSuggestionIndex = -1;
    }

    public function selectDescriptionSuggestion(int $index): void
    {
        if (isset($this->descriptionSearchResults[$index])) {
            $this->value = $this->descriptionSearchResults[$index]['description'];
            $this->showDescriptionSuggestions = false;
            $this->selectedSuggestionIndex = -1;
        }
    }

    public function hideDescriptionSuggestions(): void
    {
        $this->showDescriptionSuggestions = false;
        $this->selectedSuggestionIndex = -1;
    }

    public function navigateDescriptionSuggestions(string $direction): void
    {
        $maxIndex = count($this->descriptionSearchResults) - 1;

        if ($direction === 'down') {
            $this->selectedSuggestionIndex = $this->selectedSuggestionIndex < $maxIndex
                ? $this->selectedSuggestionIndex + 1
                : 0;
        } elseif ($direction === 'up') {
            $this->selectedSuggestionIndex = $this->selectedSuggestionIndex > 0
                ? $this->selectedSuggestionIndex - 1
                : $maxIndex;
        }
    }

    public function selectCurrentSuggestion(): void
    {
        if ($this->selectedSuggestionIndex >= 0 && isset($this->descriptionSearchResults[$this->selectedSuggestionIndex])) {
            $this->selectDescriptionSuggestion($this->selectedSuggestionIndex);
        }
    }

    public function render(): View
    {
        return view('livewire.automation-rules')
            ->layout('components.layouts.app', ['title' => 'Automation Rules']);
    }

    private function resetForm(): void
    {
        $this->editingRuleId = null;
        $this->categoryId = null;
        $this->accountId = null;
        $this->field = 'description';
        $this->operator = 'contains';
        $this->value = '';
        $this->priority = 10;
        $this->descriptionSearchResults = [];
        $this->showDescriptionSuggestions = false;
        $this->selectedSuggestionIndex = -1;
        $this->resetErrorBag();
    }
}
