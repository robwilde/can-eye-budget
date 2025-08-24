# Livewire\Exceptions\PropertyNotFoundException - Internal Server Error
Property [$getCurrentStepIndexProperty] not found on component: [import-wizard]

PHP 8.4.11
Laravel 12.25.0
localhost:8000

## Stack Trace

0 - vendor/livewire/livewire/src/Component.php:105
1 - resources/views/livewire/import-wizard.blade.php:22
2 - vendor/livewire/livewire/src/Mechanisms/ExtendBlade/ExtendedCompilerEngine.php:37
3 - vendor/livewire/livewire/src/Mechanisms/ExtendBlade/ExtendedCompilerEngine.php:38
4 - vendor/laravel/framework/src/Illuminate/View/Engines/CompilerEngine.php:76
5 - vendor/livewire/livewire/src/Mechanisms/ExtendBlade/ExtendedCompilerEngine.php:16
6 - vendor/laravel/framework/src/Illuminate/View/View.php:208
7 - vendor/laravel/framework/src/Illuminate/View/View.php:191
8 - vendor/laravel/framework/src/Illuminate/View/View.php:160
9 - vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:259
10 - vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:303
11 - vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:251
12 - vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:54
13 - vendor/livewire/livewire/src/LivewireManager.php:73
14 - vendor/livewire/livewire/src/Features/SupportPageComponents/HandlesPageComponents.php:17
15 - vendor/livewire/livewire/src/Features/SupportPageComponents/SupportPageComponents.php:117
16 - vendor/livewire/livewire/src/Features/SupportPageComponents/HandlesPageComponents.php:14
17 - vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php:46
18 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:265
19 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:211
20 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:822
21 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
22 - vendor/barryvdh/laravel-debugbar/src/Middleware/InjectDebugbar.php:66
23 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
24 - vendor/laravel/framework/src/Illuminate/Routing/Middleware/SubstituteBindings.php:50
25 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
26 - vendor/laravel/framework/src/Illuminate/Auth/Middleware/Authenticate.php:63
27 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
28 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/VerifyCsrfToken.php:87
29 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
30 - vendor/laravel/framework/src/Illuminate/View/Middleware/ShareErrorsFromSession.php:48
31 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
32 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:120
33 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:63
34 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
35 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/AddQueuedCookiesToResponse.php:36
36 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
37 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/EncryptCookies.php:74
38 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
39 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
40 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:821
41 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:800
42 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:764
43 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:753
44 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:200
45 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
46 - vendor/livewire/livewire/src/Features/SupportDisablingBackButtonCache/DisableBackButtonCacheMiddleware.php:19
47 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
48 - vendor/barryvdh/laravel-debugbar/src/Middleware/InjectDebugbar.php:66
49 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
50 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
51 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ConvertEmptyStringsToNull.php:31
52 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
53 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
54 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TrimStrings.php:51
55 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
56 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePostSize.php:27
57 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
58 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestsDuringMaintenance.php:109
59 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
60 - vendor/laravel/framework/src/Illuminate/Http/Middleware/HandleCors.php:48
61 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
62 - vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php:58
63 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
64 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/InvokeDeferredCallbacks.php:22
65 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
66 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePathEncoding.php:26
67 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
68 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
69 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:175
70 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:144
71 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1219
72 - public/index.php:22
73 - vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php:23

## Request

GET /import

## Headers

* **host**: localhost:8000
* **user-agent**: Mozilla/5.0 (X11; Linux x86_64; rv:143.0) Gecko/20100101 Firefox/143.0
* **accept**: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
* **accept-language**: en-US,en;q=0.5
* **accept-encoding**: gzip, deflate, br, zstd
* **connection**: keep-alive
* **cookie**: Phpstorm-3f5a811f=cdb23a4b-8928-4e6a-b587-3d109ce6c3c6; crisp-client%2Fsession%2F94df3bad-21cf-4960-aca6-3651f0db22f4=session_a2899811-30d7-426d-85f4-3c6e009c8662; XSRF-TOKEN=eyJpdiI6InRGeFNyM2cxY0ZXbzdtYVVrUngyWHc9PSIsInZhbHVlIjoiYy9zSnZjT2ZYWndYQ3l4NE1TSFUxREdnNkJiRmQ2UndMd0QzQ2x3L1A2a0RjNDdPK3BOd2VCUUQxWENuYmVrQWFwMDFMc0hvWTZ5MGl1aVZINXZFSHJ2ZDVnTjNOVFY1UnI0T3VMelN3TW9DbGtoSExNOHgydy94ODVqRlJFcTUiLCJtYWMiOiIwNTY5YmU4YzBiNmRhMTdmMjQ2YzlhZTczM2U3ODQ0OWNmZDI4YmFkMzJhMzU1YzRjNDQ3YTZjY2E4NDU5NGU0IiwidGFnIjoiIn0%3D; can_eye_session=eyJpdiI6Ii9aMmNBallkR2p5QWE1Ym1kL0NPZlE9PSIsInZhbHVlIjoiTDVqbDJFdWkzQWpEemEyUkNtby9DUXgwZ01zY3hubXlVL09jZHFUMnh0QURQQlFWOUJSZFhiUnhZaXVnUHZ0cWNWbEI0Z0tlMnhiR3B1SmJpdzVVRjFDa1VNNkFxWmlKemFqMzlIWHpFNGZTcnVwZW96bmZGYzZ0S3o2TzZFSHoiLCJtYWMiOiI0ZWZmNzAyYTgzMDBjNmMyNzcwNTk4YWNjN2VmZjRiYjI4YWJjMDNiNmEzZTU2NGVmZWZiZjg5NjkxZWU3NWVhIiwidGFnIjoiIn0%3D
* **upgrade-insecure-requests**: 1
* **sec-fetch-dest**: document
* **sec-fetch-mode**: navigate
* **sec-fetch-site**: none
* **sec-fetch-user**: ?1
* **priority**: u=0, i

## Route Context

controller: App\Livewire\ImportWizard
route name: import.wizard
middleware: web, auth

## Route Parameters

No route parameter data available.

## Database Queries

* sqlite - select * from "sessions" where "id" = '6H9XK4s5m6Y3Mi95WhbIr0KwtaZK82DVizp2RZCe' limit 1 (1.41 ms)
* sqlite - select * from "users" where "id" = 1 limit 1 (0.29 ms)
