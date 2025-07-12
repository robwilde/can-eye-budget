This file is a merged representation of the entire codebase, combined into a single document by Repomix.

# File Summary

## Purpose
This file contains a packed representation of the entire repository's contents.
It is designed to be easily consumable by AI systems for analysis, code review,
or other automated processes.

## File Format
The content is organized as follows:
1. This summary section
2. Repository information
3. Directory structure
4. Repository files (if enabled)
5. Multiple file entries, each consisting of:
  a. A header with the file path (## File: path/to/file)
  b. The full contents of the file in a code block

## Usage Guidelines
- This file should be treated as read-only. Any changes should be made to the
  original repository files, not this packed version.
- When processing this file, use the file path to distinguish
  between different files in the repository.
- Be aware that this file may contain sensitive information. Handle it with
  the same level of security as you would the original repository.

## Notes
- Some files may have been excluded based on .gitignore rules and Repomix's configuration
- Binary files are not included in this packed representation. Please refer to the Repository Structure section for a complete list of file paths, including binary files
- Files matching patterns in .gitignore are excluded
- Files matching default ignore patterns are excluded
- Files are sorted by Git change count (files with more changes are at the bottom)

# Directory Structure
```
_index.md
blade.md
configuration.md
installation.md
queries.md
usage.md
```

# Files

## File: _index.md
````markdown
---
title: Laravel
weight: 2
---
````

## File: blade.md
````markdown
---
title: Using Ray in Blade views
menuTitle: Blade
weight: 5
---

## Logging variables

You can use the `@ray` directive to easily send variables to Ray from inside a Blade view. You can pass as many things as you'd like.

```blade
{{-- inside a view --}}

@ray($variable, $anotherVariables)
```

## Show all variables available

You can use the `@xray` directive to show all variables available in your Blade file.

## Using measure

You can use the `@measure` directive as a shortcut for the `ray()->measure()` method to measure the time and memory it takes to render content in your view.

```blade
{{-- inside a view --}}

@measure
@php(sleep(4))
@measure
```

This will result in the following output:

![screenshot](/screenshots/measure-blade.png)
````

## File: configuration.md
````markdown
---
title: Configuration
weight: 2
---

For Laravel projects you can create a `ray.php` file in your project directory (not in the `config` directory) using the following template as [the Ray config file](/docs/php/vanilla-php/configuration). Since the configuration file is developer specific, you might want to add it to the `.gitignore` of the project.

Note: if everyone working on the project needs the same configuration, you can put the file in the `config` directory as well.

```php
<?php
// Save this in a file called "ray.php" in the root directory of your project; not in the Laravel "config" directory

return [
    /*
    * This setting controls whether data should be sent to Ray.
    *
    * By default, `ray()` will only transmit data in non-production environments.
    */
    'enable' => env('RAY_ENABLED', true),

    /*
    * When enabled, all cache events  will automatically be sent to Ray.
    */
    'send_cache_to_ray' => env('SEND_CACHE_TO_RAY', false),

    /*
    * When enabled, all things passed to `dump` or `dd`
    * will be sent to Ray as well.
    */
    'send_dumps_to_ray' => env('SEND_DUMPS_TO_RAY', true),

    /*
    * When enabled all job events will automatically be sent to Ray.
    */
    'send_jobs_to_ray' => env('SEND_JOBS_TO_RAY', false),

    /*
    * When enabled, all things logged to the application log
    * will be sent to Ray as well.
    */
    'send_log_calls_to_ray' => env('SEND_LOG_CALLS_TO_RAY', true),

    /*
    * When enabled, all queries will automatically be sent to Ray.
    */
    'send_queries_to_ray' => env('SEND_QUERIES_TO_RAY', false),

    /**
     * When enabled, all duplicate queries will automatically be sent to Ray.
     */
    'send_duplicate_queries_to_ray' => env('SEND_DUPLICATE_QUERIES_TO_RAY', false),

    /*
     * When enabled, slow queries will automatically be sent to Ray.
     */
    'send_slow_queries_to_ray' => env('SEND_SLOW_QUERIES_TO_RAY', false),

    /**
     * Queries that are longer than this number of milliseconds will be regarded as slow.
     */
    'slow_query_threshold_in_ms' => env('RAY_SLOW_QUERY_THRESHOLD_IN_MS', 500),

    /*
     * When enabled, all update queries will automatically be sent to Ray.
     */
    'send_update_queries_to_ray' => env('SEND_UPDATE_QUERIES_TO_RAY', false),

    /*
     * When enabled, all insert queries will automatically be sent to Ray.
     */
    'send_insert_queries_to_ray' => env('SEND_INSERT_QUERIES_TO_RAY', false),

    /*
     * When enabled, all delete queries will automatically be sent to Ray.
     */
    'send_delete_queries_to_ray' => env('SEND_DELETE_QUERIES_TO_RAY', false),

    /*
     * When enabled, all select queries will automatically be sent to Ray.
     */
    'send_select_queries_to_ray' => env('SEND_SELECT_QUERIES_TO_RAY', false),

    /*
    * When enabled, all requests made to this app will automatically be sent to Ray.
    */
    'send_requests_to_ray' => env('SEND_REQUESTS_TO_RAY', false),

    /**
     * When enabled, all Http Client requests made by this app will be automatically sent to Ray.
     */
    'send_http_client_requests_to_ray' => env('SEND_HTTP_CLIENT_REQUESTS_TO_RAY', false),

    /*
    * When enabled, all views that are rendered automatically be sent to Ray.
    */
    'send_views_to_ray' => env('SEND_VIEWS_TO_RAY', false),

    /*
     * When enabled, all exceptions will be automatically sent to Ray.
     */
    'send_exceptions_to_ray' => env('SEND_EXCEPTIONS_TO_RAY', true),

    /*
     * When enabled, all deprecation notices will be automatically sent to Ray.
     */
    'send_deprecated_notices_to_ray' => env('SEND_DEPRECATED_NOTICES_TO_RAY', false),

    /*
    * The host used to communicate with the Ray app.
    * When using Docker on Mac or Windows, you can replace localhost with 'host.docker.internal'
    * When using Docker on Linux, you can replace localhost with '172.17.0.1'
    * When using Homestead with the VirtualBox provider, you can replace localhost with '10.0.2.2'
    * When using Homestead with the Parallels provider, you can replace localhost with '10.211.55.2'
    */
    'host' => env('RAY_HOST', 'localhost'),

    /*
    * The port number used to communicate with the Ray app.
    */
    'port' => env('RAY_PORT', 23517),

    /*
     * Absolute base path for your sites or projects in Homestead,
     * Vagrant, Docker, or another remote development server.
     */
    'remote_path' => env('RAY_REMOTE_PATH', null),

    /*
     * Absolute base path for your sites or projects on your local
     * computer where your IDE or code editor is running on.
     */
    'local_path' => env('RAY_LOCAL_PATH', null),

    /*
     * When this setting is enabled, the package will not try to format values sent to Ray.
     */
    'always_send_raw_values' => false,
];
```

## Docker

See [our Docker-specific configuration page](/docs/environments/docker) for information about setting up Ray in combination with Docker. All changes also apply to a setup with Laravel.
````

## File: installation.md
````markdown
---
title: Using Ray With Laravel
menuTitle: Installation
weight: 1
---

If you use Laravel, this is the way.

## Installing the package in single Laravel project

```bash
composer require spatie/laravel-ray
```

By installing Ray like this it will also be installed in your production environment. This way your application will not break if you forget to remove a `ray` call.  The package will not attempt to transmit information to Ray when the app environment is set to `production`.

You could opt to install `laravel-ray` as a dev dependency. If you go this route, make sure to remove every `ray` call in the code before deploying.

```bash
composer require spatie/laravel-ray --dev
```

## Use ray(), dd() and dump() in any file.

Head over to the [global installation instructions](/docs/php/vanilla-php/installation#global-installation) to learn how to enable `ray()`, `dd()` and `dump()` in any file.

## Creating a config file

Optionally, you can run an artisan command to publish [the config file](/docs/php/laravel/configuration) in to the project root.

```bash
php artisan ray:publish-config
```

You can also add `--docker` or `--homestead` option to set up a base configuration for those dev environments.

```bash
php artisan ray:publish-config --docker
# or
php artisan ray:publish-config --homestead
```

## Using Ray in an Orchestra powered test suite

In order to use a Laravel specific functionality you must call Ray's service provider in your base test case.

```php
// add this to your base test case

protected function getPackageProviders($app)
{
    return [
        \Spatie\LaravelRay\RayServiceProvider::class,
    ];
}
```
````

## File: queries.md
````markdown
---
title: Debugging Database Queries
menuTitle: Queries
weight: 4
---

## Showing queries

You can display all queries that are executed by calling `showQueries` (or `queries`).

```php
ray()->showQueries();

// This query will be displayed in Ray.
User::firstWhere('email', 'john@example.com'); 
```

![screenshot](/screenshots/queries.png)

To stop showing queries, call `stopShowingQueries`.

```php
ray()->showQueries();

// This query will be displayed.
User::firstWhere('email', 'john@example.com'); 

ray()->stopShowingQueries();

// This query won't be displayed.
User::firstWhere('email', 'jane@example.com'); 
```

Alternatively, you can pass a callable to `showQueries`. Only the queries performed inside that callable will be displayed in Ray. If you include a return type in the callable, the return value will also be returned.

```php
// This query won't be displayed.
User::all(); 

ray()->showQueries(function() {
    // This query will be displayed.
    User::all(); 
});

$users = ray()->showQueries(function (): Illuminate\Support\Collection {
    // This query will be displayed and the collection will be returned.
    return User::all(); 
});

User::all(); // this query won't be displayed.
```

## Conditional queries

You may want to only show queries if a certain condition is met. You can pass a closure that will receive `QueryExecuted` to `showConditionalQueries`.

```php
// When a binding contains 'joan', the query will be displayed.
ray()->showConditionalQueries(fn (QueryExecuted $query) => 
    Arr::first(
        $query->bindings,
        fn ($binding) => Str::contains($binding, 'joan')
    )
);
```

This is particularly helpful when dealing with many queries during migrations or data manipulation.

Convenience methods are available for select, insert, update, and delete queries.

```php
ray()->showInsertQueries();
// Insert queries will be displayed.
ray()->stopShowingInsertQueries();

// Update queries will be displayed during execution of handleUpdate().
ray()->showUpdateQueries(fn () => $this->handleUpdate());

// Select queries will be displayed.
ray()->showSelectQueries();

// Delete queries will be displayed.
ray()->showDeleteQueries();
```

Additionally, these can be enabled in the config file.

## Counting queries

If you're interested in how many queries a given piece of code executes, and what the runtime of those queries is, you can use `countQueries`. It expects you to pass a closure in which all the executed queries will be counted.

Similar to `showQueries`, you can also add a return type to your closure to return the result of the closure.

```php
ray()->countQueries(function() {
    User::all();
    User::all();
    User::all();
});

$user = ray()->countQueries(function (): User {
    return User::where('condition', true)->first();
});
```

![screenshot](/screenshots/counting-queries.png)

## Manually showing a query

You can manually send a query to Ray by calling `ray()` on a query.

```php
User::query()
    ->where('email', 'john@example.com')
    ->ray()
    ->first();
```

![screenshot](/screenshots/showing-query.png)

You can call `ray()` multiple times to see how a query is being built up.

```php
User::query()
        ->where('name', 'John')
        ->ray()
        ->whereDate('email_verified_at', '2024-02-15')
        ->ray()
        ->first();
```

![screenshot](/screenshots/showing-query-2.png)

## Showing duplicate queries

You can display all duplicate queries by calling `showDuplicateQueries`.

```php
ray()->showDuplicateQueries();

// This query won't be displayed in Ray.
User::firstWhere('email', 'john@example.com'); 

// This query will be displayed in Ray.
User::firstWhere('email', 'john@example.com'); 
```

To stop showing duplicate queries, call `stopShowingDuplicateQueries`.

Alternatively, you can pass a callable to `showDuplicateQueries`. Only the duplicate queries performed inside that callable will be displayed in Ray.

```php
User::all();

// This query won't be displayed.
User::all(); 

ray()->showDuplicateQueries(function() {
    User::where('id', 1)->get('id');
    
    // This query will be displayed.
    User::where('id', 1)->get('id'); 
});

User::all();

// This query won't be displayed.
User::all(); 
```

## Showing slow queries

You can display all queries that took longer than a specified number of milliseconds to execute by calling `showSlowQueries`.

```php
ray()->showSlowQueries(100);

// This query will only be displayed in Ray if it takes longer than 100ms to execute.
User::firstWhere('email', 'john@example.com');
```

Alternatively, you can also pass a callable to `showSlowQueries`. Only the slow queries performed inside that callable will be displayed in Ray.

```php
User::all();

// This query won't be displayed.
User::all(); 

ray()->showSlowQueries(100, function() {
    // This query will be displayed if it takes longer than 100ms.
    User::where('id', 1)->get('id'); 
});
```

You can also use the shorthand method, `slowQueries()` which is the equivalent of calling `showSlowQueries`:

```php
ray()->slowQueries(); 
```

To stop showing slow queries, call `stopShowingSlowQueries`.
````

## File: usage.md
````markdown
---
title: Ray Methods in Laravel
menuTitle: Usage
weight: 3
---

Inside a Laravel application, you can use all methods from [the framework agnostic version](/docs/php/vanilla-php/usage).

Additionally, you can use these Laravel specific methods. Sometimes you may want to log something to Ray and get the resulting return value of your closure instead of an instance of `Ray`. You can achieve this by adding a return value type to your closure. See the examples for `showQueries()` and `countQueries()` below. Any other methods that accept a closure will function the same way.

## Showing events

You can display all events that are executed by calling `showEvents` (or `events`).

```php
ray()->showEvents();

event(new TestEvent());

event(new TestEventWithParameter('my argument'));
```

![screenshot](/screenshots/events.png)

To stop showing events, call `stopShowingEvents`.

```php
ray()->showEvents();

event(new MyEvent()); // this event will be displayed

ray()->stopShowingEvents();

event(new MyOtherEvent()); // this event won't be displayed.
```

Alternatively, you can pass a callable to `showEvents`. Only the events fired inside that callable will be displayed in Ray.

```php
event(new MyEvent()); // this event won't be displayed.

ray()->showEvents(function() {
    event(new MyEvent()); // this event will be displayed.
});

event(new MyEvent()); // this event won't be displayed.
```

## Showing jobs

You can display all jobs that are executed by calling `showJobs` (or `jobs`).

```php
ray()->showJobs();

dispatch(new TestJob('my-test-job'));

```

![screenshot](/screenshots/jobs.png)

To stop showing jobs, call `stopShowingJobs`.

```php
ray()->showJobs();

dispatch(new TestJob()); // this job will be displayed

ray()->stopShowingJobs();

dispatch(new MyTestOtherJob()); // this job won't be displayed.
```

Alternatively, you can pass a callable to `showJobs`. Only the jobs dispatch inside that callable will be displayed in Ray.

```php
event(new TestJob()); // this job won't be displayed.

ray()->showJobs(function() {
    dispatch(new TestJob()); // this job will be displayed.
});

event(new TestJob()); // this job won't be displayed.
```

## Showing cache events

You can display all cache events using `showCache`

```php
ray()->showCache();

Cache::put('my-key', ['a' => 1]);

Cache::get('my-key');

Cache::get('another-key');
```

![screenshot](/screenshots/cache.png)

To stop showing cache events, call `stopShowingCache`.

## Showing context

Laravel 11 introduced [the ability to set context](https://laravel.com/docs/11.x/context).

You can display all context using Ray's `context` method.

```php
ray()->context(); // displays all context

ray()->context('key', 'key2'); // displays only the given keys
```

![screenshot](/screenshots/context.png)

Context can also be invisible. You can display those values using the `hiddenContext` method.

```php
ray()->hiddenContext(); // displays all hidden context

ray()->hiddenContext('key', 'key2'); // displays only the given hidden keys
```

![screenshot](/screenshots/context-hidden.png)

## Handling models

Using the `model` function, you can display the attributes and relations of a model.

```php
ray()->model($user);
```

![screenshot](/screenshots/model.png)

The `model` function can also accept multiple models and even collections.

```php
// all of these models will be displayed in Ray
ray()->model($user, $anotherUser, $yetAnotherUser);

// all models in the collection will be display
ray()->model(User::all());

// all models in all collections will be displayed
ray()->model(User::all(), OtherModel::all());
```

Alternatively, you can use `models()` which is an alias for `model()`.

## Displaying mailables

Mails that are sent to the log mailer are automatically shown in Ray, you can also display the rendered version of a specific mailable in Ray by passing a mailable to the `mailable` function.

```php
ray()->mailable(new TestMailable());
```

![screenshot](/screenshots/mailable.png)

## Showing which views are rendered

You can display all views that are rendered by calling `showViews`.

```php
ray()->showViews();

// typically you'll do this in a controller
view('welcome', ['name' => 'John Doe'])->render();
```

![screenshot](/screenshots/views.png)

To stop showing views, call `stopShowingViews`.

## Displaying markdown

View the rendered version of a markdown string in Ray by calling the `markdown` function.

```php
ray()->markdown('# Hello World');
```

![screenshot](/screenshots/markdown.png)

## Displaying collections

Ray will automatically register a `ray` collection macro to easily send collections to ray.

```php
collect(['a', 'b', 'c'])
    ->ray('original collection') // displays the original collection
    ->map(fn(string $letter) => strtoupper($letter))
    ->ray('uppercased collection'); // displays the modified collection
```

![screenshot](/screenshots/collections.png)

## Usage with a Stringable

Ray will automatically register a `ray` macro to `Stringable` to easily send `Stringable`s to Ray.

```php
Str::of('Lorem')
   ->append(' Ipsum')
   ->ray()
   ->append(' Dolor Sit Amen');
```

![screenshot](/screenshots/stringable.png)


## Displaying environment variables

You can use the `env()` method to display all environment variables as loaded from your `.env` file.  You may optionally pass an array of variable names to exclusively display.

```php
ray()->env();

ray()->env(['APP_NAME', 'DB_DATABASE', 'DB_HOSTNAME', 'DB_PORT']);
```

## Using Ray with test responses

When testing responses, you can send a `TestResponse` to Ray using the `ray()` method.

`ray()` is chainable, so you can chain on any of Laravel's assertion methods.

```php
// somewhere in your app
Route::get('api/my-endpoint', function () {
    return response()->json(['a' => 1]);
});

// somewhere in a test
/** test */
public function my_endpoint_works_correctly()
{
    $this
        ->get('api/my-endpoint')
        ->ray()
        ->assertSuccessful();
}
```

![screenshot](/screenshots/response.png)

To enable this behaviour by default, you can set the `send_requests_to_ray` option in [the config file](/docs/php/laravel/configuration) to `true`.

## Showing HTTP client requests

You can display all HTTP client requests and responses using `showHttpClientRequests`

```php
ray()->showHttpClientRequests();

Http::get('https://example.com/api/users');
```

![screenshot](/screenshots/requests.png)

To stop showing HTTP client events, call `stopShowingHttpClientRequests`.

Alternatively, you can pass a callable to `showHttpClientRequests`. Only the HTTP requests made inside that callable will be displayed in Ray.

```php
Http::get('https://example.com'); // this request won't be displayed.

ray()->showHttpClientRequests(function() {
    Http::get('https://example.com'); // this request will be displayed.
});

Http::get('https://example.com'); // this request won't be displayed.
```
````
