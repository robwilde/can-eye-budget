# CategoryManager Component

The **CategoryManager** component provides comprehensive management of hierarchical categories and auto-categorization rules, including creation, editing, deletion, and rule configuration for automatic transaction categorization.

## Overview

- **Location**: `app/Livewire/CategoryManager.php`
- **Blade Template**: `resources/views/livewire/category-manager.blade.php`
- **Purpose**: Full CRUD operations for categories and category rules with hierarchy support

## Features

### Category Management
- **Hierarchical Structure**: Create nested category trees with parent-child relationships
- **CRUD Operations**: Complete create, read, update, delete functionality
- **Visual Customization**: Color and icon assignment for visual identification
- **Smart Deletion**: Safe category deletion with transaction protection and child reorganization

### Category Rules System
- **Auto-Categorization**: Rules that automatically assign categories to transactions
- **Multiple Operators**: Various matching operators for flexible rule creation
- **Priority System**: Rule execution order based on priority values
- **Field Matching**: Rules based on transaction description or amount

### Safety Features
- **Transaction Protection**: Prevents deletion of categories with existing transactions
- **Hierarchy Preservation**: Maintains tree structure during category operations
- **User Isolation**: Ensures category and rule data belongs to authenticated user

## Properties

### Component State

```php
public bool $showCategoryForm = false;      // Category form modal visibility
public bool $showRuleForm = false;          // Rule form modal visibility
public ?Category $editingCategory = null;   // Category being edited
public ?CategoryRule $editingRule = null;   // Rule being edited
```

### Category Form Fields

```php
#[Validate('required|string|max:255')]
public string $categoryName = '';                    // Category name

#[Validate('nullable|integer|exists:categories,id')]
public ?int $parentCategoryId = null;                // Parent category ID

#[Validate('nullable|string|regex:/^#[0-9A-Fa-f]{6}$/')]
public string $categoryColor = '';                   // Hex color code

#[Validate('nullable|string|max:50')]
public string $categoryIcon = '';                    // Icon identifier
```

### Rule Form Fields

```php
#[Validate('required|in:description,amount')]
public string $ruleField = 'description';           // Field to match against

#[Validate('required|in:contains,equals,starts_with,ends_with,greater_than,less_than')]
public string $ruleOperator = 'contains';           // Matching operator

#[Validate('required|string|max:255')]
public string $ruleValue = '';                      // Value to match

#[Validate('required|integer|min:1|max:999')]
public int $rulePriority = 1;                       // Rule execution priority

#[Validate('required|integer|exists:categories,id')]
public ?int $ruleCategoryId = null;                 // Target category for rule
```

### Computed Properties

```php
#[Computed] categories()       // Hierarchical category tree
#[Computed] flatCategories()   // Flat category list for selections
#[Computed] categoryRules()    // All category rules ordered by priority
```

## Methods

### Category Management

```php
openCategoryForm(?Category $category = null)    // Open category form (create/edit)
saveCategory()                                  // Save category (create or update)
deleteCategory(Category $category)             // Delete category with safety checks
closeCategoryForm()                            // Close category form and reset
```

### Rule Management

```php
openRuleForm(?CategoryRule $rule = null)       // Open rule form (create/edit)
saveRule()                                     // Save rule (create or update)
deleteRule(CategoryRule $rule)                 // Delete rule
closeRuleForm()                                // Close rule form and reset
```

### Form Utilities

```php
resetCategoryForm()                            // Reset category form fields
resetRuleForm()                                // Reset rule form fields
```

## Validation Rules

### Category Validation

```php
categoryName: 'required|string|max:255'
parentCategoryId: 'nullable|integer|exists:categories,id'
categoryColor: 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/'
categoryIcon: 'nullable|string|max:50'
```

### Rule Validation

```php
ruleField: 'required|in:description,amount'
ruleOperator: 'required|in:contains,equals,starts_with,ends_with,greater_than,less_than'
ruleValue: 'required|string|max:255'
rulePriority: 'required|integer|min:1|max:999'
ruleCategoryId: 'required|integer|exists:categories,id'
```

## Events

### Dispatched Events

```php
'category-saved' => void      // After successful category save
'category-deleted' => void    // After successful category deletion
'rule-saved' => void         // After successful rule save
'rule-deleted' => void       // After successful rule deletion
```

## Rule System

### Supported Fields
- **description**: Match against transaction description text
- **amount**: Match against transaction amount values

### Available Operators

#### Text Operators (for description field)
- **contains**: Description contains the value
- **equals**: Description exactly equals the value
- **starts_with**: Description starts with the value
- **ends_with**: Description ends with the value

#### Numeric Operators (for amount field)
- **equals**: Amount exactly equals the value
- **greater_than**: Amount is greater than the value
- **less_than**: Amount is less than the value

### Priority System
- Rules are executed in priority order (1 = highest priority)
- Lower priority numbers execute first
- First matching rule assigns the category
- Range: 1-999 priority levels

## Hierarchy Management

### Nested Set Model
- Uses nested set model for efficient tree operations
- Maintains _lft and _rgt values automatically
- Supports unlimited nesting depth
- Optimized for tree queries and operations

### Tree Operations
- **Create Root**: New category without parent
- **Create Child**: New category under existing parent
- **Move Node**: Change parent relationship
- **Delete with Reorganization**: Move children to grandparent on deletion

## Safety Features

### Transaction Protection
```php
// Prevents deletion of categories with transactions
if ($category->transactions()->count() > 0) {
    $this->addError('general', 'Cannot delete category with existing transactions.');
    return;
}
```

### Hierarchy Preservation
```php
// Reorganize children when deleting parent
foreach ($category->children as $child) {
    if ($category->parent) {
        $child->appendToNode($category->parent)->save();
    } else {
        $child->saveAsRoot();
    }
}
```

### User Isolation
- All queries scoped to authenticated user
- Category ownership validation
- Rule category ownership verification

## Usage Examples

### Basic Implementation

```html
<livewire:category-manager />
```

### With Initial Category

```html
<livewire:category-manager :editing-category="$category" />
```

### Event Listening

```javascript
// Listen for category changes
$wire.on('category-saved', () => {
    // Refresh other components
    $wire.dispatch('categories-updated');
});
```

## Data Flow

1. **Load Data**: Fetch user's categories and rules
2. **Display Tree**: Render hierarchical category structure
3. **Form Operations**: Handle create/edit operations with validation
4. **Nested Set Updates**: Maintain tree structure during modifications
5. **Event Dispatch**: Notify other components of changes

## Integration Points

### With Transaction System
- Categories used in transaction creation and editing
- Auto-categorization rules applied during transaction import
- Category deletion protection based on transaction usage

### With TransactionForm
- Provides categories for transaction assignment
- Supports dynamic category creation during transaction entry
- Updates available categories in real-time

### With Import System
- Rules applied during CSV import processing
- Automatic category assignment based on rule matching
- Priority-based rule execution for accurate categorization

## Performance Considerations

### Tree Operations
- Nested set model for efficient hierarchy queries
- Batch operations for tree modifications
- Cached tree structures for repeated access

### Rule Processing
- Priority-ordered rule execution
- Early termination on first match
- Indexed database queries for rule matching

### Data Loading
- User-scoped queries for security and performance
- Eager loading of relationships where needed
- Minimal data transfer for form operations

## Testing

### Feature Tests
- Located in `tests/Feature/CategoryManagerTest.php`
- Tests category CRUD operations
- Validates hierarchy maintenance
- Confirms rule creation and execution
- Tests safety features and edge cases

### Test Coverage
- Category creation, editing, deletion
- Hierarchy operations (parent changes, reorganization)
- Rule creation and priority handling
- Transaction protection features
- User isolation and security

## Common Issues

### Hierarchy Management
- Ensure proper nested set maintenance
- Handle circular reference prevention
- Maintain tree integrity during operations

### Rule Conflicts
- Manage rule priority conflicts
- Handle overlapping rule conditions
- Ensure deterministic rule execution

### Performance with Deep Trees
- Consider pagination for large category trees
- Optimize tree queries for deep hierarchies
- Cache frequently accessed tree structures

## Customization

### Visual Appearance
- Color-coded categories for easy identification
- Icon support for visual categorization
- Hierarchical indentation in tree display
- Responsive design for mobile devices

### Rule Extensions
- Extendable operator system
- Additional field support (future enhancement)
- Custom rule logic integration
- Machine learning rule suggestions (future)

### UI Customization
- Tailwind CSS styling framework
- Flux UI components integration
- Custom form layouts and styling
- Accessibility features and compliance