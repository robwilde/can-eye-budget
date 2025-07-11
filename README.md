# CanEye Budget - Personal Budget Management Application

A modern personal budgeting application built with Laravel 12 and PHP 8.4, featuring calendar-based views, transaction management, CSV import capabilities, and intelligent balance projections.

## Features

- **Transaction Management**: Income, expense, and transfer tracking with category organization
- **Calendar Views**: Visualize transactions across different time periods (daily, weekly, monthly, yearly)
- **CSV Import & Reconciliation**: Import bank statements with duplicate detection and auto-categorization
- **Recurring Transactions**: Set up automatic recurring income and expenses
- **Balance Projections**: Future balance calculations and trend analysis
- **Hierarchical Categories**: Nested category system for detailed transaction organization
- **Multi-Account Support**: Manage multiple bank accounts, credit cards, and cash accounts

## Tech Stack

- **Backend**: Laravel 12, PHP 8.4, SQLite (development)
- **Frontend**: Livewire, Volt, Flux UI, Tailwind CSS 4, Alpine.js
- **Testing**: PEST PHP with Laravel plugin
- **Build**: Vite with Laravel plugin
- **Data Handling**: Spatie Laravel Data for type-safe DTOs

## Installation

### Prerequisites

- PHP 8.4+
- Composer
- Node.js & npm
- SQLite

### Setup

1. Clone the repository:
```bash
git clone <repository-url>
cd can-eye-budget
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install Node.js dependencies:
```bash
npm install
```

4. Set up environment:
```bash
cp .env.example .env
php artisan key:generate
```

5. Create and migrate database:
```bash
touch database/database.sqlite
php artisan migrate
php artisan db:seed
```

6. Build assets:
```bash
npm run build
```

## Development

### Start Development Environment

Start all development services concurrently:
```bash
composer dev
```

This command starts:
- Laravel development server
- Vite development server with hot reload
- Queue worker
- Real-time log viewer

### Individual Commands

```bash
# Laravel development server
php artisan serve

# Frontend development with hot reload
npm run dev

# Queue worker for background jobs
php artisan queue:listen

# Real-time log viewer
php artisan pail
```

### Testing

```bash
# Run full test suite
composer test

# Run tests directly
php artisan test

# Run specific test
./vendor/bin/pest --filter=ExampleTest
```

### Code Quality

```bash
# Fix code style with Laravel Pint
composer pint

# Check style without fixing
./vendor/bin/pint --test
```

## Architecture

### Database Schema

The application uses a multi-entity budget model:

- **Users**: Can have multiple accounts
- **Accounts**: Different account types (checking, savings, credit)
- **Transactions**: Belong to accounts and categories with soft deletes
- **Categories**: Hierarchical structure using nested set model
- **Recurring Patterns**: Generate future transactions automatically
- **Imports**: Track CSV import history and status
- **Category Rules**: Auto-categorization based on transaction patterns

### Service Layer

Type-safe business logic using Data Transfer Objects (DTOs):

- `TransactionService`: CRUD operations and transfer handling
- `ProjectionService`: Future balance calculations
- `RecurringService`: Recurring transaction generation
- `CategoryMatchingService`: AI-like auto-categorization
- `ImportService`: CSV processing and duplicate detection

### Frontend Components

- **Livewire Volt**: Single-file components in `resources/views/livewire/`
- **Flux UI**: Design system components
- **Calendar Views**: Interactive transaction visualization
- **Import Wizard**: Step-by-step CSV import process

## Configuration

### Environment Variables

```bash
# Database
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite

# Import Settings
IMPORT_CHUNK_SIZE=100
PROJECTION_MONTHS=12
```

### Config Files

- `config/budget.php`: Application-specific settings
- `config/import.php`: CSV import mappings
- `config/categories.php`: Default category structure

## Usage

### Adding Transactions

1. Navigate to the transaction form
2. Select account, type (income/expense/transfer), and amount
3. Choose or create a category
4. Set transaction date (past, present, or future)

### CSV Import

1. Upload your bank's CSV export
2. Map columns to transaction fields
3. Preview and review duplicate detection
4. Confirm import with auto-categorization

### Recurring Transactions

1. Create a transaction pattern (daily, weekly, monthly, yearly)
2. Set occurrence limits and end dates
3. System automatically generates future transactions
4. Modify individual occurrences as needed

### Balance Projections

- View projected balances based on recurring patterns
- Identify potential negative balance periods
- Analyze income vs expense trends

## API Endpoints

RESTful API endpoints with Sanctum authentication:

```bash
# Transactions
GET /api/transactions
POST /api/transactions
PUT /api/transactions/{id}
DELETE /api/transactions/{id}

# Accounts
GET /api/accounts
POST /api/accounts

# Categories
GET /api/categories
POST /api/categories
```

## Custom Artisan Commands

```bash
# Generate recurring transactions
php artisan budget:generate-recurring

# Process CSV imports
php artisan budget:process-import {file}

# Calculate balance projections
php artisan budget:calculate-projections

# Clean old import files
php artisan budget:cleanup-imports
```

## Security

- Policy-based authorization for all resources
- XSS protection for user inputs
- CSV upload validation and sanitization
- Rate limiting for import operations
- Multi-tenancy through user scoping

## Performance

- Optimized database queries with eager loading
- Cached category hierarchy
- Chunked CSV processing
- Background job queues for large imports

## Contributing

1. Fork the repository
2. Create a feature branch
3. Write tests for new functionality
4. Ensure code passes style checks: `composer pint`
5. Run test suite: `composer test`
6. Submit a pull request

## License

This project is licensed under the MIT License.

## Support

For questions or issues, please check the documentation in the `docs/` directory or create an issue in the repository.