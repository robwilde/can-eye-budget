<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\Category;
use App\Models\CategoryRule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class CategoryManager extends Component
{
    public bool $showCategoryForm = false;

    public bool $showRuleForm = false;

    public ?Category $editingCategory = null;

    public ?CategoryRule $editingRule = null;

    // Category form fields
    #[Validate('required|string|max:255')]
    public string $categoryName = '';

    #[Validate('nullable|integer|exists:categories,id')]
    public ?int $parentCategoryId = null;

    #[Validate('nullable|string|regex:/^#[0-9A-Fa-f]{6}$/')]
    public string $categoryColor = '';

    #[Validate('nullable|string|max:50')]
    public string $categoryIcon = '';

    // Rule form fields
    #[Validate('required|in:description,amount')]
    public string $ruleField = 'description';

    #[Validate('required|in:contains,equals,starts_with,ends_with,greater_than,less_than')]
    public string $ruleOperator = 'contains';

    #[Validate('required|string|max:255')]
    public string $ruleValue = '';

    #[Validate('required|integer|min:1|max:999')]
    public int $rulePriority = 1;

    #[Validate('required|integer|exists:categories,id')]
    public ?int $ruleCategoryId = null;

    #[Computed]
    public function categories()
    {
        return auth()->user()->categories()
            ->defaultOrder()
            ->with('children')
            ->get()
            ->toTree();
    }

    #[Computed]
    public function flatCategories()
    {
        return auth()->user()->categories()
            ->defaultOrder()
            ->get();
    }

    #[Computed]
    public function categoryRules()
    {
        return CategoryRule::with('category')
            ->whereHas('category', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->byPriority()
            ->get();
    }

    public function openCategoryForm(?Category $category = null): void
    {
        $this->editingCategory = $category;

        if ($category) {
            $this->categoryName = $category->name ?? '';
            $this->parentCategoryId = $category->parent_id;
            $this->categoryColor = $category->color ?? '';
            $this->categoryIcon = $category->icon ?? '';
        } else {
            $this->resetCategoryForm();
        }

        $this->showCategoryForm = true;
    }

    public function saveCategory(): void
    {
        $this->validate([
            'categoryName'     => 'required|string|max:255',
            'parentCategoryId' => 'nullable|integer|exists:categories,id',
            'categoryColor'    => 'nullable|string',
            'categoryIcon'     => 'nullable|string|max:50',
        ]);

        $data = [
            'user_id'   => auth()->id(),
            'name'      => $this->categoryName,
            'parent_id' => $this->parentCategoryId,
            'color'     => $this->categoryColor ?: null,
            'icon'      => $this->categoryIcon ?: null,
        ];

        if ($this->editingCategory) {
            $this->editingCategory->update($data);

            // Handle parent change (move node)
            if ($this->editingCategory->parent_id !== $this->parentCategoryId) {
                if ($this->parentCategoryId) {
                    $newParent = Category::find($this->parentCategoryId);
                    $this->editingCategory->appendToNode($newParent)->save();
                } else {
                    $this->editingCategory->saveAsRoot();
                }
            }
        } else {
            if ($this->parentCategoryId) {
                $parent = Category::find($this->parentCategoryId);
                $category = new Category($data);
                $category->appendToNode($parent)->save();
            } else {
                $category = Category::create($data);
            }
        }

        $this->closeCategoryForm();
        $this->dispatch('category-saved');
    }

    public function deleteCategory(Category $category): void
    {
        // Check if category has transactions
        if ($category->transactions()->count() > 0) {
            $this->addError('general', 'Cannot delete category with existing transactions. Please reassign transactions first.');

            return;
        }

        // Move children to parent or root
        foreach ($category->children as $child) {
            if ($category->parent) {
                $child->appendToNode($category->parent)->save();
            } else {
                $child->saveAsRoot();
            }
        }

        $category->delete();
        $this->dispatch('category-deleted');
    }

    public function openRuleForm(?CategoryRule $rule = null): void
    {
        $this->editingRule = $rule;
        $this->resetRuleForm();

        if ($rule) {
            $this->ruleField = $rule->field;
            $this->ruleOperator = $rule->operator;
            $this->ruleValue = $rule->value;
            $this->rulePriority = $rule->priority;
            $this->ruleCategoryId = $rule->category_id;
        }

        $this->showRuleForm = true;
    }

    public function saveRule(): void
    {
        $this->validate([
            'ruleField'      => 'required|in:description,amount',
            'ruleOperator'   => 'required|in:contains,equals,starts_with,ends_with,greater_than,less_than',
            'ruleValue'      => 'required|string|max:255',
            'rulePriority'   => 'required|integer|min:1|max:999',
            'ruleCategoryId' => 'required|integer|exists:categories,id',
        ]);

        $data = [
            'field'       => $this->ruleField,
            'operator'    => $this->ruleOperator,
            'value'       => $this->ruleValue,
            'priority'    => $this->rulePriority,
            'category_id' => $this->ruleCategoryId,
        ];

        if ($this->editingRule) {
            $this->editingRule->update($data);
        } else {
            CategoryRule::create($data);
        }

        $this->closeRuleForm();
        $this->dispatch('rule-saved');
    }

    public function deleteRule(CategoryRule $rule): void
    {
        $rule->delete();
        $this->dispatch('rule-deleted');
    }

    public function closeCategoryForm(): void
    {
        $this->showCategoryForm = false;
        $this->resetCategoryForm();
    }

    public function closeRuleForm(): void
    {
        $this->showRuleForm = false;
        $this->resetRuleForm();
    }

    public function render()
    {
        return view('livewire.category-manager');
    }

    private function resetCategoryForm(): void
    {
        $this->editingCategory = null;
        $this->categoryName = '';
        $this->parentCategoryId = null;
        $this->categoryColor = '';
        $this->categoryIcon = '';
        $this->resetErrorBag();
    }

    private function resetRuleForm(): void
    {
        $this->editingRule = null;
        $this->ruleField = 'description';
        $this->ruleOperator = 'contains';
        $this->ruleValue = '';
        $this->rulePriority = 1;
        $this->ruleCategoryId = null;
        $this->resetErrorBag();
    }
}
