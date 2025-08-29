# TypeError - Internal Server Error
App\Data\TransactionData::__construct(): Argument #6 ($transaction_date) must be of type Carbon\Carbon, Carbon\CarbonImmutable given, called in /var/home/mrwilde/Projects/MrWilde/Apps/CanEye-Project/can-eye-budget/app/Services/ImportService.php on line 418

PHP 8.4.11
Laravel 12.25.0
localhost:8000

## Stack Trace

0 - app/Data/TransactionData.php:22
1 - app/Services/ImportService.php:418
2 - vendor/laravel/framework/src/Illuminate/Database/Concerns/ManagesTransactions.php:32
3 - vendor/laravel/framework/src/Illuminate/Database/DatabaseManager.php:497
4 - vendor/laravel/framework/src/Illuminate/Support/Facades/Facade.php:363
5 - app/Services/ImportService.php:416
6 - app/Services/ImportService.php:65
7 - app/Livewire/ImportWizard.php:173
8 - vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php:36
9 - vendor/laravel/framework/src/Illuminate/Container/Util.php:43
10 - vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php:96
11 - vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php:35
12 - vendor/livewire/livewire/src/Wrapped.php:23
13 - vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:492
14 - vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:101
15 - vendor/livewire/livewire/src/LivewireManager.php:102
16 - vendor/livewire/livewire/src/Mechanisms/HandleRequests/HandleRequests.php:94
17 - vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php:46
18 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:265
19 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:211
20 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:822
21 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
22 - vendor/barryvdh/laravel-debugbar/src/Middleware/InjectDebugbar.php:66
23 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
24 - vendor/laravel/framework/src/Illuminate/Routing/Middleware/SubstituteBindings.php:50
25 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
26 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/VerifyCsrfToken.php:87
27 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
28 - vendor/laravel/framework/src/Illuminate/View/Middleware/ShareErrorsFromSession.php:48
29 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
30 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:120
31 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:63
32 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
33 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/AddQueuedCookiesToResponse.php:36
34 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
35 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/EncryptCookies.php:74
36 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
37 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
38 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:821
39 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:800
40 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:764
41 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:753
42 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:200
43 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
44 - vendor/livewire/livewire/src/Features/SupportDisablingBackButtonCache/DisableBackButtonCacheMiddleware.php:19
45 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
46 - vendor/barryvdh/laravel-debugbar/src/Middleware/InjectDebugbar.php:66
47 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
48 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ConvertEmptyStringsToNull.php:27
49 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
50 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TrimStrings.php:47
51 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
52 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePostSize.php:27
53 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
54 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestsDuringMaintenance.php:109
55 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
56 - vendor/laravel/framework/src/Illuminate/Http/Middleware/HandleCors.php:48
57 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
58 - vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php:58
59 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
60 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/InvokeDeferredCallbacks.php:22
61 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
62 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePathEncoding.php:26
63 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
64 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
65 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:175
66 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:144
67 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1219
68 - public/index.php:22
69 - vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php:23

## Request

POST /livewire/update

## Headers

* **host**: localhost:8000
* **user-agent**: Mozilla/5.0 (X11; Linux x86_64; rv:143.0) Gecko/20100101 Firefox/143.0
* **accept**: */*
* **accept-language**: en-US,en;q=0.5
* **accept-encoding**: gzip, deflate, br, zstd
* **referer**: http://localhost:8000/import
* **content-type**: application/json
* **x-livewire**:
* **content-length**: 6135
* **origin**: http://localhost:8000
* **connection**: keep-alive
* **cookie**: Phpstorm-3f5a811f=cdb23a4b-8928-4e6a-b587-3d109ce6c3c6; crisp-client%2Fsession%2F94df3bad-21cf-4960-aca6-3651f0db22f4=session_a2899811-30d7-426d-85f4-3c6e009c8662; XSRF-TOKEN=eyJpdiI6Im4xVTIxL21yS1ZsRFl3WGtNNHR2Nmc9PSIsInZhbHVlIjoiZjhqWWJWYmpZazhMREFxbjRac1FHNmwrZ1FjWVJUN29yMlJ2dTJWSEF0V3RtTEZPRko0K3lmUVVjeUx1VmNZdklId0xTZW1mbEtZMW1URUtMLzRqanBIYWxGaDhLeWVFYU1MakswdVJQVndmMi9DK0cweVhBTDRVbTYrNE5lVWYiLCJtYWMiOiIyZGU1NmU0YWVmYzhjMjI2MDNiZjQyNDgyYzcxYTc0MGI0Mzc5OGFkYTBmNTdiZjU3YTYwMDVkZjk1OWE5OGJmIiwidGFnIjoiIn0%3D; can_eye_session=eyJpdiI6Ik9pM29yd0NsdnFaNVdnNHJKTExQVmc9PSIsInZhbHVlIjoiV1BpeEVjQ0ZvcTBxaTNSQjBYUko3N3Rtc1J5ZzErODZLSUxFckMrV1pyZUFxbm5ZRDc5NFdvK0hBY3JsWXp2NFVadXNXRWw2RmNsZ1FZZWdYOVN0S3BDU2Y5a0J0S1dUK1l1am5sSzdNT2Z5NzNoSHBnR091MkVQWThhaFZ3U2QiLCJtYWMiOiI3N2ZlOGYzN2E3ODZkNmYwMzg1NTQ3NjhhNjZiMWNjZmNlZGQxNmU4NGQwZjliZjkwODJkODhhMmM5YjM2OTZhIiwidGFnIjoiIn0%3D
* **sec-fetch-dest**: empty
* **sec-fetch-mode**: cors
* **sec-fetch-site**: same-origin
* **priority**: u=4

## Route Context

controller: Livewire\Mechanisms\HandleRequests\HandleRequests@handleUpdate
route name: livewire.update
middleware: web

## Route Parameters

No route parameter data available.

## Database Queries

* sqlite - select * from "sessions" where "id" = 'fKMIvhF0nbFledE5uSfUZ0Xy6LkvOssq35FwFDO6' limit 1 (0.39 ms)
* sqlite - select * from "users" where "id" = 1 limit 1 (0.06 ms)
* sqlite - select * from "accounts" where "accounts"."id" = 10 limit 1 (0.06 ms)
* sqlite - insert into "imports" ("user_id", "filename", "csv_file_path", "imported_at", "status", "updated_at", "created_at") values (1, 'StatementCsv_03_2025.csv', 'imports/RxJxTaH9Rslj05LQU5qD6ElQ6K8Pc3PENdAbZysC.csv', '2025-08-25 12:00:37', 'pending', '2025-08-25 12:00:37', '2025-08-25 12:00:37') (32.04 ms)
* sqlite - update "imports" set "status" = 'processing', "updated_at" = '2025-08-25 12:00:37' where "id" = 19 (29.6 ms)
* sqlite - select * from "transactions" where "transactions"."account_id" = 10 and "transactions"."account_id" is not null and "transaction_date" between '2025-02-28 12:00:37' and '2025-08-26 12:00:37' and "transactions"."deleted_at" is null (0.17 ms)
* sqlite - select * from "users" where "users"."id" = 1 limit 1 (0.11 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.08 ms)
* sqlite - select * from "category_rules" where exists (select * from "categories" where "category_rules"."category_id" = "categories"."id" and "user_id" = 1) order by "priority" desc (0.11 ms)
* sqlite - insert into "cache" ("expiration", "key", "value") values (1756126837, 'can_eye_cache_category_rules_user_1', 'TzozOToiSWxsdW1pbmF0ZVxEYXRhYmFzZVxFbG9xdWVudFxDb2xsZWN0aW9uIjoyOntzOjg6IgAqAGl0ZW1zIjthOjA6e31zOjI4OiIAKgBlc2NhcGVXaGVuQ2FzdGluZ1RvU3RyaW5nIjtiOjA7fQ==') on conflict ("key") do update set "expiration" = "excluded"."expiration", "key" = "excluded"."key", "value" = "excluded"."value" (29.35 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.09 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.05 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.05 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.05 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.04 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
* sqlite - select * from "cache" where "key" in ('can_eye_cache_category_rules_user_1') (0.03 ms)
