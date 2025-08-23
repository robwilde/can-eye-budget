<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Category;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class CategoriesPage extends Component
{
    #[Validate('required|string|max:255')]
    public string $newCategoryName = '';

    public string $searchTerm = '';

    public string $currentAutocomplete = '';
    public int $autocompleteIndex = 0;

    public function addCategory(): void
    {
        $this->validate();

        try {
            $category = $this->createCategoryFromPath($this->newCategoryName);
            
            session()->flash('message', "Category '{$category->getFullNameAttribute()}' created successfully.");
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating category: ' . $e->getMessage());
        }

        $this->newCategoryName = '';
        $this->autocompleteIndex = 0;
    }

    public function updatedNewCategoryName()
    {
        // Reset autocomplete index when the input changes
        $this->autocompleteIndex = 0;
    }

    public function deleteCategory(Category $category): void
    {
        $hasTransactions = $category->transactions()->exists();

        if ($hasTransactions) {
            session()->flash('error', 'Cannot delete category that has transactions associated with it.');

            return;
        }

        $hasChildren = $category->children()->exists();

        if ($hasChildren) {
            session()->flash('error', 'Cannot delete category that has subcategories. Please delete subcategories first.');

            return;
        }

        $category->delete();

        session()->flash('message', 'Category deleted successfully.');
    }

    #[Computed]
    public function categories()
    {
        $query = Category::forUser(auth()->id())
            ->withCount('transactions');

        if ($this->searchTerm) {
            $query->where('name', 'like', '%'.$this->searchTerm.'%');
        }

        return $query->defaultOrder()
            ->get()
            ->toTree();
    }

    #[Computed]
    public function matchingSuggestions()
    {
        if (empty($this->newCategoryName)) {
            return collect();
        }

        $searchTerm = mb_strtolower($this->newCategoryName);

        return Category::forUser(auth()->id())
            ->withCount('transactions')
            ->get()
            ->filter(function ($category) use ($searchTerm) {
                return str_contains(mb_strtolower($category->getFullNameAttribute()), $searchTerm);
            })
            ->take(5);
    }

    #[Computed]
    public function autocompleteOptions()
    {
        if (empty($this->newCategoryName)) {
            return collect();
        }

        // Split the current input by '/' to understand the hierarchy level
        $parts = explode('/', $this->newCategoryName);
        $currentPart = array_pop($parts); // The part we're currently typing
        $parentPath = implode('/', $parts); // The parent path

        // Get all categories for this user
        $allCategories = Category::forUser(auth()->id())->get();

        if (empty($parentPath)) {
            // We're at the root level, show top-level categories
            $candidates = $allCategories->filter(function ($category) {
                return is_null($category->parent_id);
            });
        } else {
            // We're in a subcategory, find the parent and show its children
            $parentCategory = $allCategories->first(function ($category) use ($parentPath) {
                return mb_strtolower($category->getFullNameAttribute()) === mb_strtolower($parentPath);
            });

            if (!$parentCategory) {
                return collect();
            }

            $candidates = $allCategories->filter(function ($category) use ($parentCategory) {
                return $category->parent_id === $parentCategory->id;
            });
        }

        // Filter candidates by the current part being typed
        if (!empty($currentPart)) {
            $candidates = $candidates->filter(function ($category) use ($currentPart) {
                return str_starts_with(mb_strtolower($category->name), mb_strtolower($currentPart));
            });
        }

        return $candidates->sortBy('name')->values();
    }

    #[Computed]
    public function currentAutocompleteSuggestion()
    {
        $options = $this->autocompleteOptions();
        
        if ($options->isEmpty()) {
            return null;
        }

        $index = $this->autocompleteIndex % $options->count();
        $selectedCategory = $options->get($index);

        if (!$selectedCategory) {
            return null;
        }

        // Build the full path for the suggestion
        $parts = explode('/', $this->newCategoryName);
        array_pop($parts); // Remove the current partial part
        $parts[] = $selectedCategory->name;

        return implode('/', $parts);
    }

    public function acceptAutocomplete()
    {
        $suggestion = $this->currentAutocompleteSuggestion;
        
        if ($suggestion) {
            $this->newCategoryName = $suggestion;
            $this->autocompleteIndex = 0;
        }
    }

    public function nextAutocomplete()
    {
        if ($this->autocompleteOptions()->isNotEmpty()) {
            $this->autocompleteIndex = ($this->autocompleteIndex + 1) % $this->autocompleteOptions()->count();
        }
    }

    public function previousAutocomplete()
    {
        $count = $this->autocompleteOptions()->count();
        if ($count > 0) {
            $this->autocompleteIndex = ($this->autocompleteIndex - 1 + $count) % $count;
        }
    }

    public function render()
    {
        return view('livewire.categories-page')
            ->layout('components.layouts.app', ['title' => 'Categories']);
    }

    private function createCategoryFromPath(string $path): Category
    {
        $parts = array_map('trim', explode('/', $path));
        $parent = null;

        foreach ($parts as $part) {
            if (empty($part)) {
                continue;
            }

            // First try exact match (case-sensitive)
            $category = Category::forUser(auth()->id())
                ->where('name', $part)
                ->where('parent_id', $parent?->id)
                ->first();

            // If not found, try case-insensitive match
            if (! $category) {
                $category = Category::forUser(auth()->id())
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($part)])
                    ->where('parent_id', $parent?->id)
                    ->first();
            }

            // If still not found, create new category
            if (! $category) {
                $category = Category::create([
                    'user_id'   => auth()->id(),
                    'name'      => $part,
                    'parent_id' => $parent?->id,
                    'color'     => $this->generateCategoryColor(),
                    'icon'      => $this->getDefaultIcon(),
                ]);
            }

            $parent = $category;
        }

        return $parent;
    }

    private function generateCategoryColor(): string
    {
        $colors = [
            '#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16',
            '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9',
            '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef',
            '#ec4899', '#f43f5e',
        ];

        return $colors[array_rand($colors)];
    }

    private function getDefaultIcon(): string
    {
        return 'tag';
    }
}
