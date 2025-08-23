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

    public function addCategory(): void
    {
        $this->validate();

        $this->createCategoryFromPath($this->newCategoryName);

        session()->flash('message', 'Category created successfully.');

        $this->newCategoryName = '';
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
            ->get()
            ->filter(function ($category) use ($searchTerm) {
                return str_contains(mb_strtolower($category->getFullNameAttribute()), $searchTerm);
            })
            ->take(5);
    }

    public function render()
    {
        return view('livewire.categories-page')
            ->layout('components.layouts.app.sidebar');
    }

    private function createCategoryFromPath(string $path): Category
    {
        $parts = array_map('trim', explode('/', $path));
        $parent = null;

        foreach ($parts as $part) {
            if (empty($part)) {
                continue;
            }

            $category = Category::forUser(auth()->id())
                ->where('name', $part)
                ->where('parent_id', $parent?->id)
                ->first();

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
