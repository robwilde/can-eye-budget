# CalendarView Component

The **CalendarView** component is the primary interface for viewing transactions in calendar format with multiple view modes, balance calculations, and interactive navigation.

## Overview

- **Location**: `app/Livewire/CalendarView.php`
- **Blade Template**: `resources/views/livewire/calendar-view.blade.php`
- **Purpose**: Display transactions in calendar layouts with projections and filtering

## Features

### View Modes
- **Day**: Single day detailed view
- **Week**: 7-day week view
- **Month**: Full month calendar grid
- **Year**: 12-month overview with monthly totals

### Core Functionality
- Interactive date navigation (previous/next/today)
- Account filtering (all accounts or specific account)
- Transaction display with color coding
- Balance calculations (current and projected)
- Transaction creation via date clicks
- Real-time updates from other components

## Properties

### Public Properties

```php
public string $view = 'month';           // Current view mode
public Carbon $currentDate;              // Currently displayed date
public ?int $selectedAccountId = null;   // Selected account filter
```

### Computed Properties

```php
#[Computed] accounts()        // User's accounts collection
#[Computed] selectedAccount() // Currently selected account object
#[Computed] transactions()    // Transactions for current date range
#[Computed] projections()     // Balance projections array
#[Computed] balances()        // Account balances (current/projected)
```

## Methods

### View Management

```php
setView(string $view)           // Change view mode ('day', 'week', 'month', 'year')
getViewTitle()                  // Get formatted title for current view
```

### Navigation

```php
previousPeriod()                // Navigate to previous period
nextPeriod()                    // Navigate to next period  
goToToday()                     // Jump to current date
```

### Account Filtering

```php
selectAccount(?int $accountId)  // Filter by account (null = all accounts)
```

### Transaction Management

```php
openTransactionForm(?int $transactionId = null)     // Open transaction form modal
openTransactionFormForDate(string $date)            // Open form for specific date
```

## Events

### Dispatched Events

```php
'view-changed' => $view                    // When view mode changes
'open-transaction-form' => $transactionId  // Request to open transaction form
'open-transaction-form-for-date' => $date  // Request form for specific date
```

### Listened Events

```php
'transaction-created' => '$refresh'        // Refresh when transaction created
'transaction-updated' => '$refresh'        // Refresh when transaction updated  
'transaction-deleted' => '$refresh'        // Refresh when transaction deleted
```

## Dependencies

### Services
- **ProjectionService**: Calculates balance projections and future balances
- **CalendarHelper**: Utility methods for date range calculations and formatting

### Models
- **Account**: User accounts for filtering and balance calculations
- **Transaction**: Transaction data with relationships to categories and accounts

## Usage Examples

### Basic Implementation

```html
<livewire:calendar-view />
```

### With Default View

```html
<livewire:calendar-view view="week" />
```

### With Account Pre-selected

```html
<livewire:calendar-view :selected-account-id="$accountId" />
```

## Data Flow

1. **Mount**: Initialize current date to now
2. **Load Data**: Fetch accounts and transactions for date range
3. **Calculate Projections**: Use ProjectionService for balance forecasting
4. **Render View**: Display appropriate calendar layout
5. **Handle Events**: Respond to navigation and transaction events

## Performance Considerations

### Optimizations
- Computed properties cache expensive operations
- Eager loading of relationships (`category`, `transferToAccount`)
- Grouped transaction queries by date
- Conditional account filtering in database queries

### Data Loading
- Date range queries limit transaction scope
- Account filtering reduces dataset size
- Projected balances calculated on-demand
- Helper service handles complex date calculations

## Integration Points

### With TransactionForm
- Listens for transaction CRUD events
- Dispatches form open requests
- Provides date context for new transactions

### With ProjectionService  
- Requests balance projections for date ranges
- Handles both account-specific and user-wide projections
- Supports different projection types per view mode

### With CalendarHelper
- Delegates date range calculations
- Uses helper for period navigation
- Formats view titles through helper methods

## Customization

### View Templates
Each view mode uses a separate template:
- `calendar-view-day.blade.php`
- `calendar-view-week.blade.php` 
- `calendar-view-month.blade.php`
- `calendar-view-year.blade.php`

### Styling
- Uses Tailwind CSS classes
- Color-coded transactions (income/expense/transfer)
- Responsive design for mobile devices
- Flux UI components for consistent styling

### Event System
- Easily extendable with additional event listeners
- Decoupled communication with other components
- Real-time updates without page refresh

## Testing

### Feature Tests
- Located in `tests/Feature/CalendarViewTest.php`
- Tests all view modes and navigation
- Verifies transaction display and filtering
- Confirms balance calculations accuracy

### Test Coverage
- View mode switching
- Date navigation (previous/next/today)
- Account filtering functionality
- Transaction display verification
- Balance calculation accuracy
- Event dispatching and listening

## Common Issues

### Performance
- Large date ranges may slow rendering
- Consider pagination for accounts with many transactions
- Cache projections for frequently accessed periods

### Date Handling
- Ensure proper timezone handling across views
- Carbon date formatting consistency
- Date range boundary calculations

### State Management
- Component state persists during navigation
- Account selection maintained across view changes
- Current date preserved during refreshes