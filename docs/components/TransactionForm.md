# TransactionForm Component

The **TransactionForm** component provides a comprehensive modal interface for creating, editing, and deleting transactions with support for all transaction types and dynamic category creation.

## Overview

- **Location**: `app/Livewire/TransactionForm.php`
- **Blade Template**: `resources/views/livewire/transaction-form.blade.php`
- **Purpose**: Full CRUD operations for transactions with rich form validation and category management

## Features

### Transaction Management
- **Create**: New income, expense, and transfer transactions
- **Edit**: Modify existing transactions with full field support
- **Delete**: Remove transactions with confirmation
- **Validation**: Comprehensive form validation with error handling

### Transaction Types
- **Income**: Positive amounts with category support
- **Expense**: Regular expenses with category assignment
- **Transfer**: Account-to-account transfers with dual account selection

### Dynamic Category Management
- **Inline Creation**: Create categories without leaving the form
- **Hierarchy Support**: Assign parent categories during creation
- **Color Selection**: Visual category identification
- **Real-time Updates**: Categories available immediately after creation

## Properties

### Form Fields

```php
#[Validate('required|integer|exists:accounts,id')]
public ?int $account_id = null;                     // Source account

#[Validate('required|in:income,expense,transfer')]
public string $type = 'expense';                    // Transaction type

#[Validate('required|numeric|min:0|max:999999.99')]
public ?float $amount = null;                       // Transaction amount

#[Validate('required|string|max:255')]
public string $description = '';                    // Transaction description

#[Validate('required|date')]
public string $transaction_date = '';               // Transaction date (Y-m-d)

#[Validate('nullable|integer|exists:categories,id')]
public ?int $category_id = null;                    // Category assignment

#[Validate('nullable|integer|exists:accounts,id')]
public ?int $transfer_to_account_id = null;         // Transfer destination

public bool $reconciled = false;                    // Reconciliation status
```

### Component State

```php
public ?Transaction $transaction = null;            // Transaction being edited
public bool $isOpen = false;                        // Modal visibility
public string $mode = 'create';                     // 'create' or 'edit' mode
```

### Category Creation

```php
public bool $showCategoryForm = false;              // Category form visibility
public string $newCategoryName = '';                // New category name
public ?int $newCategoryParentId = null;            // New category parent
public string $newCategoryColor = '#3b82f6';       // New category color
```

### Computed Properties

```php
#[Computed] accounts()         // Available accounts (filtered for transfers)
#[Computed] transferAccounts() // Transfer destination accounts
#[Computed] categories()       // Hierarchical category tree
#[Computed] flatCategories()   // Flat category list for parent selection
```

## Methods

### Modal Management

```php
open(?Transaction $transaction = null)    // Open modal (create or edit mode)
close()                                   // Close modal and reset form
```

### Transaction Operations

```php
save()                                    // Create or update transaction
delete()                                  // Delete current transaction
```

### Category Management

```php
showNewCategoryForm()                     // Display inline category creation form
createCategory()                          // Create new category and assign to transaction
```

### Event Handlers

```php
openFromEvent(?int $transactionId = null) // Open via event (from other components)
openForDate(string $date)                 // Open with pre-filled date
updatedType()                             // Handle transaction type changes
updatedAccountId()                        // Handle account selection changes
```

## Validation Rules

### Basic Transaction Fields

```php
account_id: 'required|integer|exists:accounts,id'
type: 'required|in:income,expense,transfer'
amount: 'required|numeric|min:0|max:999999.99'
description: 'required|string|max:255'
transaction_date: 'required|date'
category_id: 'nullable|integer|exists:categories,id'
```

### Transfer-Specific Validation

```php
transfer_to_account_id: 'required|integer|exists:accounts,id|different:account_id'
```

### Category Creation Validation

```php
newCategoryName: 'required|string|max:255'
newCategoryParentId: 'nullable|integer|exists:categories,id'
newCategoryColor: 'required|string|regex:/^#[0-9A-Fa-f]{6}$/'
```

### Security Validation
- Account ownership verification
- Category ownership verification
- Cross-user data protection

## Events

### Dispatched Events

```php
'transaction-created' => $transactionId    // After successful creation
'transaction-updated' => $transactionId    // After successful update
'transaction-deleted' => $transactionId    // After successful deletion
```

### Listened Events

```php
'open-transaction-form' => $transactionId          // Open form for transaction
'open-transaction-form-for-date' => $date          // Open form for specific date
```

## Dependencies

### Services
- **TransactionService**: Handles business logic for CRUD operations
- **TransactionData**: Spatie Laravel Data object for type-safe data transfer

### Models
- **Transaction**: Primary model for transaction operations
- **Category**: Dynamic category creation and hierarchy management
- **Account**: Source and destination account management

## Usage Examples

### Basic Implementation

```html
<livewire:transaction-form />
```

### With Pre-loaded Transaction

```html
<livewire:transaction-form :transaction="$transaction" />
```

### Event-Driven Opening

```javascript
// From other Livewire components
$wire.dispatch('open-transaction-form', transactionId);
$wire.dispatch('open-transaction-form-for-date', '2024-01-15');
```

## Data Flow

1. **Mount**: Initialize form with transaction data (edit mode) or defaults (create mode)
2. **Validation**: Real-time validation on form fields
3. **Save**: Process through TransactionService with comprehensive validation
4. **Events**: Dispatch success events to update other components
5. **Close**: Reset form state and hide modal

## Form Behavior

### Smart Account Filtering
- Transfer destination excludes source account
- Updates dynamically when source account changes
- Maintains user account ownership security

### Category Integration
- Inline category creation without form interruption
- Immediate availability of new categories
- Hierarchical parent selection support
- Color-coded visual organization

### Transaction Type Handling
- **Type Changes**: Automatically clears irrelevant fields
- **Transfer Mode**: Shows destination account selection
- **Income/Expense**: Focuses on category selection
- **Field Dependencies**: Smart field enabling/disabling

## Error Handling

### Validation Errors
- Real-time field validation
- Server-side validation backup
- User-friendly error messages
- Field-specific error highlighting

### Business Logic Errors
- Account ownership verification
- Category ownership verification
- Transfer account validation
- Amount and date validation

### Exception Handling
- Service layer exception catching
- User-friendly error display
- Form state preservation on errors
- Graceful degradation

## Security Features

### Multi-Tenancy Protection
- User account ownership verification
- Category ownership validation
- Cross-user data access prevention
- Secure account and category queries

### Input Validation
- Amount limits and type validation
- Date format validation
- Category and account existence checks
- Transfer account difference validation

## Integration Points

### With CalendarView
- Responds to form opening events
- Provides date context for new transactions
- Refreshes calendar on transaction changes

### With TransactionService
- Delegates business logic to service layer
- Uses TransactionData for type safety
- Handles complex transaction operations

### With Category System
- Dynamic category creation
- Hierarchical category selection
- Real-time category updates
- Category ownership security

## Performance Considerations

### Computed Properties
- Cached account and category queries
- Filtered data for optimal selection lists
- Lazy loading of non-essential data

### Form Optimization
- Minimal re-renders on field changes
- Efficient validation cycles
- Smart dependency updates

### Data Loading
- User-scoped queries for security and performance
- Ordered data for consistent UI
- Tree structure caching for categories

## Testing

### Feature Tests
- Located in `tests/Feature/TransactionFormTest.php`
- Covers all CRUD operations
- Tests transaction type handling
- Validates security measures

### Test Coverage
- Transaction creation and editing
- Account and category validation
- Transfer functionality
- Dynamic category creation
- Error handling and validation
- Event dispatching

## Common Issues

### Form State Management
- Reset form properly between operations
- Handle modal state correctly
- Clear validation errors on close

### Validation Timing
- Balance client and server validation
- Handle async validation properly
- Provide immediate feedback

### Transfer Logic
- Prevent self-transfers
- Update account lists dynamically
- Validate destination account ownership

## Customization

### Form Layout
- Tailwind CSS styling
- Flux UI components integration
- Responsive design patterns
- Custom field arrangements

### Validation Messages
- Custom error message formatting
- Field-specific validation rules
- Internationalization support
- User experience optimization