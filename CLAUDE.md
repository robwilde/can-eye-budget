# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a personal budgeting application built with Laravel 12 and PHP 8.4, using the Livewire starter kit with Flux UI components. The application features
calendar-based views for income/expenses, projections, and bank reconciliation capabilities.

## Documentation References

- The PEST PHP Docs are available for reference: `docs/packages/repomix-pestphp-docs.md`
- Playwright functions available in PEST for browser testing can be found `docs/guide/playwright-functions-available-pest.md`
- Debug tools documentation:
    - `barryvdh/laravel-debugbar` reference: http://phpdebugbar.com/docs/all.html
    - `spatie\laravel-ray` reference: https://context7.com/spatie/myray.app/llms.txt

## Essential Commands

### Development Environment Status

**IMPORTANT**: The Laravel application is always running in a separate terminal session via `composer dev`.

- The dev server is typically running on http://localhost:8000
- DO NOT start additional Laravel servers unless specifically requested
- View logs in real-time using the existing terminal or `storage/logs/laravel.log`
- Changes to PHP files are automatically detected (no restart needed)
- Vite hot reload handles frontend asset changes

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

# PEST 4 Features
./vendor/bin/pest --coverage # Generate code coverage report
./vendor/bin/pest --mutate   # Run mutation testing (requires XDEBUG_MODE=coverage)
./vendor/bin/pest --mutate --covered-only --min=60  # Mutation testing with 60% minimum score
./vendor/bin/pest --parallel # Run tests in parallel

# Browser Testing (PEST 4)
op pest-headed tests/Browser/  # Run browser tests in headed mode (uses op.conf alias)
PEST_BROWSER_HEADLESS=false ./vendor/bin/pest tests/Browser/ --headed  # Direct command
PEST_BROWSER_SLOWMO=1000 ./vendor/bin/pest tests/Browser/ # Slow motion for debugging
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

### Debugging & Cache Management

```bash
# Clear caches when encountering issues
php artisan cache:clear      # Clear application cache
php artisan config:clear     # Clear configuration cache
php artisan view:clear       # Clear compiled views
php artisan route:clear      # Clear route cache

# Check logs for errors
tail -f storage/logs/laravel.log   # Follow log in real-time
tail -20 storage/logs/laravel.log  # View last 20 lines
```

## Architecture Overview

### Tech Stack

- **Backend**: Laravel 12, PHP 8.4, SQLite (development)
- **Frontend**: Livewire, Volt, Flux UI, Tailwind CSS 4, Alpine.js
- **Testing**: PEST PHP with Laravel plugin
- **Build**: Vite with Laravel plugin

### Key Components

#### Livewire Integration

- Uses **Traditional Livewire Components** in `app/Livewire/`
- Standard Laravel routes for page views in `routes/web.php`
- Blade templates stored separately in `resources/views/`

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
app/Livewire/              # Traditional Livewire components
resources/views/livewire/   # Blade templates for Livewire components
resources/views/components/ # Blade components
resources/views/flux/       # Custom Flux UI components
tests/Feature/             # Feature tests with RefreshDatabase
tests/Unit/               # Unit tests
tests/Browser/            # PEST 4 browser tests
tests/Concerns/           # Shared test traits and utilities
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

- PEST PHP 4 with Laravel integration
- RefreshDatabase for Feature tests
- SQLite in-memory database for testing
- Factory pattern for test data generation
- Browser testing with real data copying for realistic UI tests
- Mutation testing for test quality assurance
- Architecture testing for code structure enforcement
- Compact output format for cleaner test results

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
- Testing: PEST PHP 4 with Laravel plugin, mutation testing, architecture testing
- Build: Vite, TailwindCSS 4, Laravel Vite plugin
- Quality: Laravel Pint, security advisories

## PEST 4 Features

### Browser Testing

Browser testing is configured for Distrobox container environments and includes real data integration:

```bash
# Run browser tests in headed mode (see browser interactions)
op pest-headed tests/Browser/

# Run with specific environment variables
PEST_BROWSER_HEADLESS=false ./vendor/bin/pest tests/Browser/ --headed

# Debug with slow motion
PEST_BROWSER_SLOWMO=1000 ./vendor/bin/pest tests/Browser/
```

**Real Data Integration**: Browser tests can copy real database data using the `UseRealDataForBrowserTests` trait, ensuring realistic UI testing scenarios with actual user data, transactions, and categories.

### Mutation Testing

Mutation testing is available to assess test quality by introducing small changes to code and verifying tests catch them:

```bash
# Run mutation testing with coverage
XDEBUG_MODE=coverage ./vendor/bin/pest --mutate

# Run mutation testing only on covered code with minimum score requirement
XDEBUG_MODE=coverage ./vendor/bin/pest --mutate --covered-only --min=70

# Run mutation testing on specific classes
XDEBUG_MODE=coverage ./vendor/bin/pest --mutate --class=App\\Models
```

### Architecture Testing

Architecture presets are automatically applied via `tests/Pest.php`:

- Laravel preset: Enforces Laravel best practices
- Security preset: Prevents insecure coding patterns
- Custom rules: Enforces naming conventions and inheritance patterns

### Test Coverage

Use `covers()` function in tests to specify which classes are being tested for mutation testing:

```php
covers(App\Models\User::class);

test('user model test', function () {
    // test implementation
});
```

### Browser Test Configuration

Browser tests are configured to work in Distrobox container environments:

- **Environment Variables**: Configure via `.env.testing` (APP_KEY, database settings)
- **Real Data Trait**: Use `UseRealDataForBrowserTests` trait to copy `database/database.sqlite` to test environment
- **Container Setup**: Playwright runs in Ubuntu container, Laravel app runs on host
- **Network Configuration**: Tests connect to host application via container networking

Example browser test setup:
```php
uses(UseRealDataForBrowserTests::class);

beforeEach(function () {
    $this->copyRealDatabaseForBrowserTest();
    $this->user = User::first(); // Use real user data
});
```

### Special Features

- Concurrent development workflow via `composer dev`
- Flux UI component system for consistent design
- Volt for simplified Livewire development
- PEST for expressive testing syntax

## Development Progress

For detailed progress tracking, development status, and task lists, see [`docs/todo/development-progress.md`](docs/todo/development-progress.md).

**Current Status**: Phase 4 (Import & Reconciliation) in progress at 65% completion. Overall project is ~45% complete.

===

`<laravel-boost-guidelines>`
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the
user's satisfaction building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by
these specific packages & versions.

- php - 8.4.12
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- livewire/flux (FLUXUI_FREE) - v2
- livewire/livewire (LIVEWIRE) - v3
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- tailwindcss (TAILWINDCSS) - v4

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure,
  approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

## Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs

- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and
  their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of
  packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms

=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `<code-snippet>public function __construct(public GitHub $github) { }</code-snippet>`
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

```
<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>
```

## Comments

- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks

- Add useful array shape type definitions for arrays when appropriate.

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the
  `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct
  behavior.

### Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check
  the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application
  convention.

### Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not
  `env('APP_NAME')`.

### Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the
  model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should
  be feature tests.

### Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run
  `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure

- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and
  lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== fluxui-free/core rules ===

## Flux UI Free

- This project is using the free edition of Flux UI. It has full access to the free components and variants, but does not have access to the Pro components.
- Flux UI is a component library for Livewire. Flux is a robust, hand-crafted, UI component library for your Livewire applications. It's built using Tailwind
  CSS and provides a set of components that are easy to use and customize.
- You should use Flux UI components when available.
- Fallback to standard Blade components if Flux is unavailable.
- If available, use Laravel Boost's `search-docs` tool to get the exact documentation and code snippets available for this project.
- Flux UI components look like this:

```
<code-snippet name="Flux UI Component Usage Example" lang="blade">
    <flux:button variant="primary"/>
</code-snippet>
```

### Available Components

This is correct as of Boost installation, but there may be additional components within the codebase.

```
<available-flux-components>
avatar, badge, brand, breadcrumbs, button, callout, checkbox, dropdown, field, heading, icon, input, modal, navbar, profile, radio, select, separator, switch, text, textarea, tooltip
</available-flux-components>
```

=== livewire/core rules ===

## Livewire Core

- Use the `search-docs` tool to find exact version specific documentation for how to write Livewire & Livewire tests.
- Use the `php artisan make:livewire [Posts\\CreatePost]` artisan command to create new components
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend, they're like regular HTTP requests. Always validate form data, and run authorization checks in Livewire
  actions.

## Livewire Best Practices

- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()`) for initialization and reactive side effects:

```
<code-snippet name="Lifecycle hook examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>
```

## Testing Livewire

```
<code-snippet name="Example Livewire component test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>
```

```
<code-snippet name="Testing a Livewire component exists within a page" lang="php">
    $this->get('/posts/create')
    ->assertSeeLivewire(CreatePost::class);
</code-snippet>
```

=== livewire/v3 rules ===

## Livewire 3

### Key Changes From Livewire 2

- These things changed in Livewire 2, but may not have been updated in this application. Verify this application's setup to ensure you conform with application
  conventions.
    - Use `wire:model.live` for real-time updates, `wire:model` is now deferred by default.
    - Components now use the `App\Livewire` namespace (not `App\Http\Livewire`).
    - Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
    - Use the `components.layouts.app` view as the typical layout path (not `layouts.app`).

### New Directives

- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use. Use the documentation to find usage examples.

### Alpine

- Alpine is now included with Livewire, don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

### Lifecycle Hooks

- You can listen for `livewire:init` to hook into Livewire initialization, and `fail.status === 419` for the page expiring:

```
<code-snippet name="livewire:load example" lang="js">
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });

});
</code-snippet>
```

=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== pest/core rules ===

## Pest

### Testing

- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests

- All tests must be written using Pest. Use `php artisan make:test --pest <name>`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the
  application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:

```
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
expect(true)->toBeTrue();
});
</code-snippet>
```

### Running Tests

- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions

- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or
  similar, e.g.:

```
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);
    $response->assertSuccessful();
});
</code-snippet>
```

### Mocking

- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively,
  you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets

- Use datasets in Pest to simplify tests which have a lot of duplicated data. This is often the case when testing validation rules, so consider going with this
  solution when writing tests for validation rules.

```
<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>
```

=== pest/v4 rules ===

## Pest 4

- Pest v4 is a huge upgrade to Pest and offers: browser testing, smoke testing, visual regression testing, test sharding, and faster type coverage.
- Browser testing is incredibly powerful and useful for this project.
- Browser tests should live in `tests/Browser/`.
- Use the `search-docs` tool for detailed guidance on utilizing these features.

### Browser Testing

- You can use Laravel features like `Event::fake()`, `assertAuthenticated()`, and model factories within Pest v4 browser tests, as well as `RefreshDatabase` (
  when needed) to ensure a clean state for each test.
- Interact with the page (click, type, scroll, select, submit, drag-and-drop, touch gestures, etc.) when appropriate to complete the test.
- If requested, test on multiple browsers (Chrome, Firefox, Safari).
- If requested, test on different devices and viewports (like iPhone 14 Pro, tablets, or custom breakpoints).
- Switch color schemes (light/dark mode) when appropriate.
- Take screenshots or pause tests for debugging when appropriate.

### Example Tests

```
<code-snippet name="Pest Browser Test Example" lang="php">
it('may reset the password', function () {
    Notification::fake();

    $this->actingAs(User::factory()->create());

    $page = visit('/sign-in'); // Visit on a real browser...

    $page->assertSee('Sign In')
        ->assertNoJavascriptErrors() // or ->assertNoConsoleLogs()
        ->click('Forgot Password?')
        ->fill('email', 'nuno@laravel.com')
        ->click('Send Reset Link')
        ->assertSee('We have emailed your password reset link!')

    Notification::assertSent(ResetPassword::class);

});
</code-snippet>
```

```
<code-snippet name="Pest Smoke Testing Example" lang="php">
$pages = visit(['/', '/about', '/contact']);

$pages->assertNoJavascriptErrors()->assertNoConsoleLogs();
</code-snippet>
```

=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group
  elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing

- When listing items, use gap utilities for spacing, don't use margins.

```
<code-snippet name="Valid Flex Gap Spacing Example" lang="html">
    <div class="flex gap-8">
        <div>Superior</div>
        <div>Michigan</div>
        <div>Erie</div>
    </div>
</code-snippet>
```

### Dark Mode

- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.

=== tailwindcss/v4 rules ===

## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

```
<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff"

- @tailwind base;
- @tailwind components;
- @tailwind utilities;

+ @import "tailwindcss";
</code-snippet>
```

### Replaced Utilities

- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated | Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |

=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test` with a specific filename or filter.
  </laravel-boost-guidelines>
