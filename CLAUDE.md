# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a personal budgeting application built with Laravel 12 and PHP 8.4, using the Livewire starter kit with Flux UI components. The application features calendar-based views for income/expenses, projections, and bank reconciliation capabilities.

## Essential Commands

### Development
```bash
# Start full development environment (server, queue, logs, vite)
composer dev

# Start individual services
php artisan serve              # Laravel development server
npm run dev                   # Vite development server with hot reload
php artisan queue:listen      # Queue worker
php artisan pail              # Real-time log viewer
```

### Build & Assets
```bash
npm run build                 # Production build with Vite
npm run dev                   # Development build with hot reload
```

### Testing
```bash
composer test                 # Run full test suite (clears config + runs tests)
php artisan test             # Run tests directly
./vendor/bin/pest            # Run PEST tests specifically
./vendor/bin/pest --filter=ExampleTest  # Run specific test
```

### Code Quality
```bash
composer pint                 # Fix code style with Laravel Pint
./vendor/bin/pint            # Direct Pint execution
./vendor/bin/pint --test     # Check style without fixing
```

### Database
```bash
php artisan migrate          # Run migrations
php artisan migrate:fresh --seed  # Fresh migration with seeding
php artisan db:seed          # Run seeders
```

## Architecture Overview

### Tech Stack
- **Backend**: Laravel 12, PHP 8.4, SQLite (development)
- **Frontend**: Livewire, Volt, Flux UI, Tailwind CSS 4, Alpine.js
- **Testing**: PEST PHP with Laravel plugin
- **Build**: Vite with Laravel plugin

### Key Components

#### Livewire Integration
- Uses **Livewire Volt** for single-file components in `resources/views/livewire/`
- Volt routes defined in `routes/web.php` using `Volt::route()`
- VoltServiceProvider mounts both `livewire/` and `pages/` directories

#### UI Framework
- **Flux UI** components for consistent design system
- Custom Flux components in `resources/views/flux/`
- Tailwind CSS 4 with Vite plugin integration

#### Authentication
- Laravel Breeze-style authentication with Livewire
- Auth routes in `routes/auth.php`
- Settings pages: profile, password, appearance

### File Structure Patterns
```
app/Http/Livewire/          # Traditional Livewire components
resources/views/livewire/   # Volt single-file components
resources/views/components/ # Blade components
resources/views/flux/       # Custom Flux UI components
tests/Feature/             # Feature tests with RefreshDatabase
tests/Unit/               # Unit tests
```

## Development Workflow

### Budget Application Context
Refer to `docs/budget-app-context.md` for comprehensive feature requirements including:
- Transaction management (income, expense, transfer)
- Recurring transaction patterns
- Hierarchical category system
- Calendar views with projections
- CSV import and reconciliation
- Database schema for accounts, transactions, categories

### Database Design
The application follows a multi-entity budget model:
- Users can have multiple accounts
- Transactions belong to accounts and categories
- Categories support hierarchical nesting
- Recurring patterns generate future transactions
- Import tracking for CSV reconciliation

### Testing Strategy
- PEST PHP with Laravel integration
- RefreshDatabase for Feature tests
- SQLite in-memory database for testing
- Factory pattern for test data generation

### Code Style
- Laravel Pint for PHP code formatting
- Uses PHP 8.4 features and syntax
- Follows Laravel conventions and best practices

## Key Configuration

### Environment
- SQLite database: `database/database.sqlite`
- Testing uses in-memory SQLite
- Vite with TailwindCSS and Laravel plugins

### Dependencies
- Core: Laravel 12, Livewire, Volt, Flux UI
- Testing: PEST PHP with Laravel plugin
- Build: Vite, TailwindCSS 4, Laravel Vite plugin
- Quality: Laravel Pint, security advisories

### Special Features
- Concurrent development workflow via `composer dev`
- Flux UI component system for consistent design
- Volt for simplified Livewire development
- PEST for expressive testing syntax

## Development Task List

### Phase 1: Foundation & Database
1. **Database Schema Implementation**
   - Create migrations for accounts, categories, transactions, recurring_patterns, imports, category_rules
   - Implement nested set model for categories (_lft, _rgt columns)
   - Add proper indexes for performance (date, category fields)
   - Create database seeders with default categories

2. **Core Models & Relationships**
   - `Account` model (checking, savings, credit types)
   - `Category` model with nested set trait for hierarchy
   - `Transaction` model with scopes for filtering
   - `RecurringPattern` model for handling recurring logic
   - `Import` model for CSV import tracking
   - `CategoryRule` model for auto-categorization

3. **Model Factories & Testing Foundation**
   - Create factories for all models with realistic test data
   - Set up base test cases with RefreshDatabase
   - Implement user scoping for multi-tenancy

### Phase 2: Core Services & Business Logic
4. **Service Layer Implementation**
   - `TransactionService` - CRUD operations and business rules
   - `ProjectionService` - Calculate future balances and projections
   - `RecurringService` - Generate recurring transactions
   - `CategoryMatchingService` - Auto-categorization logic
   - `ImportService` - Handle CSV processing and duplicate detection

5. **Repository Pattern**
   - `TransactionRepository` - Optimized data access with eager loading
   - `CategoryRepository` - Hierarchy queries and caching
   - `RecurringRepository` - Pattern management and generation

### Phase 3: User Interface & Livewire Components
6. **Main Dashboard & Calendar View**
   - `CalendarView` Livewire component (today, week, month, year views)
   - Interactive navigation with period switching
   - Running balance calculations and display
   - Visual indicators (green/red for income/expense)

7. **Transaction Management UI**
   - `TransactionForm` component for add/edit operations
   - Bulk operations interface
   - Date picker with past/present/future support
   - Category selection with dynamic creation

8. **Category Management**
   - `CategoryManager` component with hierarchical display
   - Drag-drop for category organization
   - Color and icon selection
   - Category rules configuration interface

### Phase 4: Import & Reconciliation Features
9. **CSV Import System**
   - `ImportWizard` Livewire component
   - File upload with validation
   - Column mapping interface
   - Preview and confirmation steps

10. **Reconciliation Workflow**
    - Duplicate detection algorithms
    - Manual reconciliation interface
    - Auto-categorization based on rules
    - Import history and status tracking

### Phase 5: Recurring Transactions & Projections
11. **Recurring Transaction System**
    - Pattern configuration (daily, weekly, monthly, yearly, custom)
    - End date and occurrence skip functionality
    - Background job for generating future transactions
    - Individual occurrence modification

12. **Balance Projections & Analytics**
    - `BalanceChart` component with Chart.js/ApexCharts
    - Future balance calculations
    - Negative balance warnings
    - Money in/out trend analysis

### Phase 6: Reporting & Advanced Features
13. **Reports & Analytics Dashboard**
    - Category breakdown reports
    - Budget vs actual comparisons
    - Trend analysis over time periods
    - Export functionality (PDF, CSV)

14. **Advanced UI/UX Features**
    - Mobile-responsive calendar stacking
    - Drag-drop transaction moving
    - Keyboard shortcuts and quick actions
    - Search and filtering capabilities

### Phase 7: Performance & Polish
15. **Performance Optimization**
    - Implement caching for category hierarchy
    - Queue large CSV imports
    - Add pagination for transaction lists
    - Database query optimization

16. **Security & Validation**
    - Policy-based authorization for all resources
    - XSS protection for user inputs
    - CSV upload validation and sanitization
    - Rate limiting for import operations

### Phase 8: Configuration & Artisan Commands
17. **Application Configuration**
    - Create `config/budget.php` for app settings
    - Create `config/import.php` for CSV mappings
    - Create `config/categories.php` for defaults
    - Add .env variables (IMPORT_CHUNK_SIZE, PROJECTION_MONTHS, etc.)

18. **Custom Artisan Commands**
    - `make:recurring-transactions` - Generate daily recurring transactions
    - `make:import-processor {file}` - Process CSV imports
    - `make:calculate-projections` - Update balance projections
    - `budget:cleanup-imports` - Clean old import files

### Future Enhancements (Optional)
19. **API Development**
    - RESTful API endpoints for mobile app
    - Sanctum authentication
    - API rate limiting and documentation

20. **Bank Integration Preparation**
    - Webhook handlers for real-time updates
    - OAuth flow structure for bank connections
    - Security vault for credentials

### Testing & Quality Assurance
- Unit tests for all services and calculations
- Feature tests for complete user workflows
- Performance testing for calendar rendering
- Security testing for file uploads and user inputs