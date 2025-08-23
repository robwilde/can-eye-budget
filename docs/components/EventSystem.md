# Event System Documentation

The **Event System** provides decoupled communication between Livewire components using Laravel's event dispatching and listening mechanisms. This enables real-time updates and coordination between components without tight coupling.

## Overview

The budget application uses Livewire's event system to:
- **Synchronize Data**: Keep components in sync when data changes
- **Trigger Actions**: Request actions from other components
- **Update UI**: Refresh components when related data is modified
- **Modal Management**: Control modal states across the application

## Event Architecture

### Event Flow Pattern

```
Component A (dispatcher) → Event → Component B (listener) → Action → UI Update
```

### Communication Types
- **One-to-Many**: One component notifies multiple listeners
- **One-to-One**: Direct communication between specific components
- **Broadcast**: System-wide notifications for global updates

## Core Events

### Transaction Events

#### `transaction-created`
```php
$this->dispatch('transaction-created', $transactionId);
```
- **Dispatcher**: TransactionForm
- **Listeners**: CalendarView
- **Purpose**: Refresh calendar when new transaction is created
- **Payload**: Transaction ID

#### `transaction-updated`
```php
$this->dispatch('transaction-updated', $transactionId);
```
- **Dispatcher**: TransactionForm
- **Listeners**: CalendarView
- **Purpose**: Refresh calendar when transaction is modified
- **Payload**: Transaction ID

#### `transaction-deleted`
```php
$this->dispatch('transaction-deleted', $transactionId);
```
- **Dispatcher**: TransactionForm
- **Listeners**: CalendarView
- **Purpose**: Refresh calendar when transaction is removed
- **Payload**: Transaction ID

### Form Control Events

#### `open-transaction-form`
```php
$this->dispatch('open-transaction-form', $transactionId);
```
- **Dispatcher**: CalendarView
- **Listeners**: TransactionForm
- **Purpose**: Open transaction form for editing
- **Payload**: Transaction ID (null for new transaction)

#### `open-transaction-form-for-date`
```php
$this->dispatch('open-transaction-form-for-date', $date);
```
- **Dispatcher**: CalendarView
- **Listeners**: TransactionForm
- **Purpose**: Open form with pre-filled date
- **Payload**: Date string (Y-m-d format)

### Category Events

#### `category-saved`
```php
$this->dispatch('category-saved');
```
- **Dispatcher**: CategoryManager
- **Listeners**: TransactionForm, other category-dependent components
- **Purpose**: Refresh category lists when category is created/updated
- **Payload**: None

#### `category-deleted`
```php
$this->dispatch('category-deleted');
```
- **Dispatcher**: CategoryManager
- **Listeners**: TransactionForm, other category-dependent components
- **Purpose**: Refresh category lists when category is removed
- **Payload**: None

### Rule Events

#### `rule-saved`
```php
$this->dispatch('rule-saved');
```
- **Dispatcher**: CategoryManager
- **Listeners**: Import components, rule-dependent systems
- **Purpose**: Update auto-categorization systems
- **Payload**: None

#### `rule-deleted`
```php
$this->dispatch('rule-deleted');
```
- **Dispatcher**: CategoryManager
- **Listeners**: Import components, rule-dependent systems
- **Purpose**: Update auto-categorization systems
- **Payload**: None

### View Events

#### `view-changed`
```php
$this->dispatch('view-changed', $view);
```
- **Dispatcher**: CalendarView
- **Listeners**: Related dashboard components
- **Purpose**: Notify when calendar view mode changes
- **Payload**: View mode string ('day', 'week', 'month', 'year')

## Event Implementation

### Dispatching Events

#### Basic Event Dispatch
```php
public function save(): void
{
    // Save logic here...
    
    // Dispatch event to notify other components
    $this->dispatch('transaction-created', $transaction->id);
}
```

#### Event with Multiple Parameters
```php
public function moveTransaction(int $transactionId, string $newDate): void
{
    // Move logic here...
    
    $this->dispatch('transaction-moved', [
        'transactionId' => $transactionId,
        'oldDate' => $oldDate,
        'newDate' => $newDate
    ]);
}
```

### Listening to Events

#### Method-based Listeners
```php
protected function getListeners(): array
{
    return [
        'transaction-created' => '$refresh',
        'transaction-updated' => '$refresh', 
        'transaction-deleted' => '$refresh',
        'open-transaction-form' => 'openFromEvent',
        'category-saved' => 'refreshCategories',
    ];
}
```

#### Handler Methods
```php
public function openFromEvent(?int $transactionId = null): void
{
    if ($transactionId) {
        $transaction = Transaction::find($transactionId);
        $this->open($transaction);
    } else {
        $this->open();
    }
}

public function refreshCategories(): void
{
    // Force recomputation of computed properties
    unset($this->categories);
    unset($this->flatCategories);
}
```

## Event Patterns

### Refresh Pattern
```php
// Automatic component refresh
'transaction-created' => '$refresh'

// Custom refresh logic
'category-saved' => 'refreshData'
```

### Modal Control Pattern
```php
// Open modal with data
'open-form' => 'openModal'

// Close modal
'close-form' => 'closeModal'

// Toggle modal state
'toggle-modal' => 'toggleModal'
```

### Data Synchronization Pattern
```php
// Clear cached computed properties
public function syncData(): void
{
    unset($this->computedProperty);
    $this->dispatch('$refresh');
}
```

## Component Event Maps

### CalendarView
**Dispatches:**
- `view-changed` → View mode changes
- `open-transaction-form` → Request form opening
- `open-transaction-form-for-date` → Request form with date

**Listens:**
- `transaction-created` → Refresh calendar
- `transaction-updated` → Refresh calendar  
- `transaction-deleted` → Refresh calendar

### TransactionForm
**Dispatches:**
- `transaction-created` → Notify of new transaction
- `transaction-updated` → Notify of transaction changes
- `transaction-deleted` → Notify of transaction removal

**Listens:**
- `open-transaction-form` → Open for editing
- `open-transaction-form-for-date` → Open with date
- `category-saved` → Refresh category list

### CategoryManager
**Dispatches:**
- `category-saved` → Notify of category changes
- `category-deleted` → Notify of category removal
- `rule-saved` → Notify of rule changes
- `rule-deleted` → Notify of rule removal

**Listens:**
- None (primarily a data source component)

## Best Practices

### Event Naming
- Use kebab-case for event names
- Include entity type and action: `entity-action`
- Be descriptive but concise: `transaction-created`, not `new-transaction-was-created`

### Event Payloads
```php
// Good: Minimal, necessary data
$this->dispatch('transaction-updated', $transaction->id);

// Bad: Large objects or unnecessary data
$this->dispatch('transaction-updated', $transaction->toArray());
```

### Error Handling
```php
public function handleEvent($data): void
{
    try {
        // Event handling logic
    } catch (Exception $e) {
        // Log error, show user feedback
        $this->addError('general', 'Failed to process update');
    }
}
```

### Performance Considerations
```php
// Use specific refresh instead of full component refresh
'data-changed' => 'refreshSpecificData'

// Rather than
'data-changed' => '$refresh'
```

## Advanced Event Patterns

### Conditional Event Dispatch
```php
public function saveTransaction(): void
{
    $wasNew = !$this->transaction->exists;
    
    // Save logic...
    
    if ($wasNew) {
        $this->dispatch('transaction-created', $this->transaction->id);
    } else {
        $this->dispatch('transaction-updated', $this->transaction->id);
    }
}
```

### Event Chaining
```php
public function processImport(): void
{
    // Process import...
    
    $this->dispatch('import-processed', $import->id);
    // This might trigger 'transactions-imported' from listener
    // Which might trigger 'balances-updated' from another listener
}
```

### Batched Events
```php
public function bulkUpdate(array $transactionIds): void
{
    // Perform bulk update...
    
    // Single event for multiple changes
    $this->dispatch('transactions-bulk-updated', $transactionIds);
}
```

## Event Debugging

### Enable Livewire Event Logging
```php
// In AppServiceProvider::boot()
if (app()->environment('local')) {
    \Livewire\Livewire::listen('component.dehydrate', function ($component) {
        if (property_exists($component, 'listeners')) {
            logger('Livewire listeners', [
                'component' => get_class($component),
                'listeners' => $component->getListeners()
            ]);
        }
    });
}
```

### Debug Event Flow
```php
public function debugDispatch(string $event, ...$parameters): void
{
    logger('Event dispatched', [
        'event' => $event,
        'parameters' => $parameters,
        'component' => static::class
    ]);
    
    $this->dispatch($event, ...$parameters);
}
```

## Testing Events

### Testing Event Dispatch
```php
public function test_transaction_creation_dispatches_event(): void
{
    Livewire::test(TransactionForm::class)
        ->set('amount', 100)
        ->set('description', 'Test')
        ->call('save')
        ->assertDispatched('transaction-created');
}
```

### Testing Event Listeners
```php
public function test_calendar_refreshes_on_transaction_update(): void
{
    $component = Livewire::test(CalendarView::class);
    
    $component->dispatch('transaction-updated', 123);
    
    // Assert component refreshed or specific method called
    $component->assertSet('refreshed', true);
}
```

### Mock Event Listeners
```php
public function test_handles_missing_transaction_gracefully(): void
{
    Livewire::test(TransactionForm::class)
        ->dispatch('open-transaction-form', 999999) // Non-existent ID
        ->assertHasNoErrors();
}
```

## Future Enhancements

### Global Event Bus
- Consider implementing Laravel Echo for real-time events
- WebSocket support for multi-user scenarios
- Event persistence for offline capabilities

### Event Metadata
- Add timestamps to events
- Include user context in events
- Event replay capabilities for debugging

### Event Validation
- Schema validation for event payloads
- Type checking for event parameters
- Event versioning for backward compatibility