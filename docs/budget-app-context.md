# Budget Application - Development Context

## Project Overview
A comprehensive personal budgeting application built with Laravel 12 and PHP 8.4, using SQLite for development and PEST for testing. The application provides calendar-based views of income/expenses with projections and bank reconciliation features.

## Technology Stack
- **Backend**: Laravel 12, PHP 8.4
- **Database**: SQLite (development), MySQL/PostgreSQL (production)
- **Frontend**: Blade, Livewire, Alpine.js, Tailwind CSS
- **Testing**: PEST PHP
- **Charts**: Chart.js or ApexCharts
- **Date Handling**: Carbon
- **CSV Processing**: Laravel Excel (maatwebsite/excel)

## Core Features

### 1. Transaction Management
- Types: income, expense, transfer
- Manual entry for any date (past/present/future)
- Bulk operations support
- Soft deletes for data integrity

### 2. Recurring Transactions
- Patterns: daily, weekly, bi-weekly, monthly, yearly, custom
- End date support
- Skip/modify individual occurrences
- Generate future projections

### 3. Category System
- Hierarchical structure (nested categories)
- Example: Bills > Electricity, Bills > Rent
- Dynamic creation during transaction entry
- Category rules for auto-assignment

### 4. Calendar Views
- Views: today, week, pay cycle, month, year
- Interactive navigation
- Visual indicators for income/expense crossover
- Running balance display
- Net position indicator (green/red)

### 5. Bank Import & Reconciliation
- CSV file upload
- Column mapping interface
- Duplicate detection
- Auto-categorization based on rules
- Manual reconciliation workflow

### 6. Reporting & Analytics
- Money in/out graphs
- Category breakdowns
- Trend analysis
- Budget vs actual comparisons

## Database Schema

```sql
-- accounts table
- id
- name
- type (checking, savings, credit)
- initial_balance
- currency
- created_at
- updated_at

-- categories table (nested set model)
- id
- name
- parent_id
- _lft
- _rgt
- color
- icon
- created_at
- updated_at

-- transactions table
- id
- account_id
- type (income, expense, transfer)
- amount
- description
- transaction_date
- category_id
- transfer_to_account_id (nullable)
- recurring_pattern_id (nullable)
- import_id (nullable)
- reconciled
- created_at
- updated_at

-- recurring_patterns table
- id
- name
- type (income, expense, transfer)
- amount
- description
- category_id
- account_id
- transfer_to_account_id (nullable)
- frequency (daily, weekly, monthly, etc)
- frequency_interval
- start_date
- end_date (nullable)
- last_generated_date
- is_active
- created_at
- updated_at

-- imports table
- id
- filename
- imported_at
- row_count
- matched_count
- status
- created_at
- updated_at

-- category_rules table
- id
- category_id
- field (description, amount)
- operator (contains, equals, greater_than)
- value
- priority
- created_at
- updated_at
```

## Key Laravel Components

### Models
- `Transaction` - Core transaction model with scopes for filtering
- `Category` - Uses nested set trait for hierarchy
- `RecurringPattern` - Handles recurring logic
- `Account` - Manages account balances
- `Import` - Tracks import history
- `CategoryRule` - Auto-categorization rules

### Services
- `TransactionService` - Business logic for transactions
- `ProjectionService` - Calculate future balances
- `RecurringService` - Generate recurring transactions
- `ImportService` - Handle CSV imports
- `CategoryMatchingService` - Auto-categorization

### Repositories
- `TransactionRepository` - Data access for transactions
- `CategoryRepository` - Category queries with hierarchy
- `RecurringRepository` - Recurring pattern management

### Livewire Components
- `CalendarView` - Main calendar interface
- `TransactionForm` - Add/edit transactions
- `ImportWizard` - CSV import workflow
- `CategoryManager` - Category CRUD
- `BalanceChart` - Money in/out visualization

## Development Guidelines

### Code Structure
```
app/
├── Models/
├── Services/
├── Repositories/
├── Http/
│   ├── Controllers/
│   └── Livewire/
├── Actions/
├── Enums/
└── Rules/

resources/
├── views/
│   ├── livewire/
│   └── components/
└── js/
    └── components/

tests/
├── Unit/
├── Feature/
└── Pest/
```

### Testing Strategy
- Unit tests for services and calculations
- Feature tests for user workflows
- Database tests with RefreshDatabase
- Factory patterns for test data
- Mock external dependencies

### Performance Considerations
- Eager loading for calendar views
- Cache category hierarchy
- Queue large CSV imports
- Database indexes on date and category fields
- Pagination for transaction lists

### Security Considerations
- Multi-tenancy through user scoping
- Policy-based authorization
- XSS protection in transaction descriptions
- CSRF protection on all forms
- Validate CSV uploads

## API Design (Future)

### RESTful Endpoints
```
GET    /api/transactions
POST   /api/transactions
PUT    /api/transactions/{id}
DELETE /api/transactions/{id}

GET    /api/categories
POST   /api/categories
PUT    /api/categories/{id}
DELETE /api/categories/{id}

POST   /api/imports/csv
GET    /api/imports/{id}/status

GET    /api/projections/{period}
GET    /api/reports/{type}
```

## Future Features

### Bank API Integration
- Plaid/Yodlee integration
- OAuth flow for bank connections
- Webhook handlers for real-time updates
- Transaction sync service
- Security vault for credentials

### Android App Integration
- API authentication with Sanctum
- Google Wallet capture endpoint
- Push notifications for unmatched transactions
- Offline capability with sync

## Development Workflow

1. Start with database migrations
2. Build models with relationships
3. Create service layer for business logic
4. Implement Livewire components
5. Add PEST tests for each feature
6. Iterate based on testing

## Commands to Create

```bash
# Artisan commands
php artisan make:recurring-transactions
php artisan make:import-processor {file}
php artisan make:calculate-projections

# Schedulable tasks
- Generate recurring transactions daily
- Clean up old imports
- Send budget alerts
```

## Configuration

### .env additions
```
IMPORT_CHUNK_SIZE=1000
PROJECTION_MONTHS=12
DEFAULT_CURRENCY=USD
ENABLE_BANK_SYNC=false
```

### Config files
- `config/budget.php` - App-specific settings
- `config/import.php` - CSV import mappings
- `config/categories.php` - Default categories

## Key Algorithms

### Balance Projection
- Start with current balance
- Add confirmed transactions
- Add recurring transaction projections
- Calculate daily running balance
- Identify negative balance dates

### Category Matching
1. Check exact description match
2. Apply regex rules in priority order
3. Use machine learning (future)
4. Fall back to manual selection

### Duplicate Detection
- Compare amount + date (within range)
- Fuzzy match descriptions
- Flag potential duplicates
- User confirms matches

## UI/UX Considerations

### Calendar View
- Color coding: green (income), red (expense), blue (transfer)
- Running balance tooltip on hover
- Drag-drop to move transactions
- Click to add transaction on date
- Visual indicators for recurring

### Mobile Responsiveness
- Stack calendar on small screens
- Swipe gestures for navigation
- Bottom sheet for transaction entry
- Simplified graph views

## Performance Metrics
- Page load: < 200ms
- CSV import: 1000 rows/second
- Calendar render: < 100ms
- API response: < 50ms
