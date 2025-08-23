The Categories page is looking good. There is how ever an error.

When I attempt to add a new category and start typing, on the second character it throws an error in the console:

# Illuminate\Database\Eloquent\MissingAttributeException - Internal Server Error
The attribute [transactions_count] either does not exist or was not retrieved for model [App\Models\Category].

PHP 8.4.11
Laravel 12.25.0
127.0.0.1:8000

## Stack Trace

<!--[if BLOCK]><![endif]-->0 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Concerns/HasAttributes.php:514
1 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Concerns/HasAttributes.php:494
2 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:2408
3 - resources/views/livewire/categories-page.blade.php:54
4 - vendor/livewire/livewire/src/Mechanisms/ExtendBlade/ExtendedCompilerEngine.php:37
5 - vendor/livewire/livewire/src/Mechanisms/ExtendBlade/ExtendedCompilerEngine.php:38
6 - vendor/laravel/framework/src/Illuminate/View/Engines/CompilerEngine.php:76
7 - vendor/livewire/livewire/src/Mechanisms/ExtendBlade/ExtendedCompilerEngine.php:16
8 - vendor/laravel/framework/src/Illuminate/View/View.php:208
9 - vendor/laravel/framework/src/Illuminate/View/View.php:191
10 - vendor/laravel/framework/src/Illuminate/View/View.php:160
11 - vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:259
12 - vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:303
13 - vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:251
14 - vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:104
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
<!--[if ENDBLOCK]><![endif]-->

## Request

POST /livewire/update

## Headers

<!--[if BLOCK]><![endif]-->* **host**: 127.0.0.1:8000
* **user-agent**: Mozilla/5.0 (X11; Linux x86_64; rv:143.0) Gecko/20100101 Firefox/143.0
* **accept**: */*
* **accept-language**: en-US,en;q=0.5
* **accept-encoding**: gzip, deflate, br, zstd
* **referer**: http://127.0.0.1:8000/categories
* **content-type**: application/json
* **x-livewire**:
* **content-length**: 461
* **origin**: http://127.0.0.1:8000
* **connection**: keep-alive
* **cookie**: userToken=1rn2enyse2yc762i5z6muo; CSRF-Token-KLFCA5Z=rWGbHJWbrWdfJ5jwrVMn7huKzfW7s4mce7wXonzZUUHbECXpaSHiZgW3jUyRXJgX; XSRF-TOKEN=eyJpdiI6InBWbS92TllSNlVPc2RwTDZFMUJMcnc9PSIsInZhbHVlIjoieEJsOVRpMGlRSmNyNktMYWo1VlYwb1JtRXRaTmx2V3hudnNzbXYvSjZieXk1TmgzK1NIQnhCNzAyQnh0MmE1bGJXbGg3Nnh0UnRTaHlPSzN4RzdKSUE2Qm0vVEt4MjNISTZ2cVdqY3VwMXZuME1IdFpscGRuL1VrMkNOMGRaNXEiLCJtYWMiOiJmZGY2YTA5MGEzOTFmNWVmNzNkYWE3YjM4ZDdhZjFlZTJmMjhjZjgwNTAxMmY0YjdlY2Y0ZGRlYzQwMmY0NTRhIiwidGFnIjoiIn0%3D; laravel_session=eyJpdiI6ImNZOEZ3MVlOTmpsMEE2REVMN3phR0E9PSIsInZhbHVlIjoiMW9pdG1FMkliS0IvT2RGeWFHWHR3ZWZDRnFNSGNrUWtMRFhrajRxV0lmRkxwb2J4cjIxM2NWbEFQU1gxZW4yZVVwU1RIWEVkNnFHTmlJQU01M0JvOS81YlFhZ0EyN00vUzFrSkkyNVRZRldkc2ZTTXhpbXI5eDdMRllTeU1UVmYiLCJtYWMiOiI5Y2QyY2IwZTNmMzVmOTA1ZWQ1MGM1YWI5Y2Q5YjU2ZjAwMjc0ODU0MTRhZTFlZTU1ODVhZDExZmMwMzVhZWM1IiwidGFnIjoiIn0%3D
* **sec-fetch-dest**: empty
* **sec-fetch-mode**: cors
* **sec-fetch-site**: same-origin
* **priority**: u=4
<!--[if ENDBLOCK]><![endif]-->

## Route Context

<!--[if BLOCK]><![endif]-->controller: Livewire\Mechanisms\HandleRequests\HandleRequests@handleUpdate
route name: livewire.update
middleware: web
<!--[if ENDBLOCK]><![endif]-->

## Route Parameters

<!--[if BLOCK]><![endif]-->No route parameter data available.
<!--[if ENDBLOCK]><![endif]-->

## Database Queries

<!--[if BLOCK]><![endif]-->* sqlite - select * from "sessions" where "id" = 'yMpUTV0T2B58oiduq6sX4pdVzHIIO8Q6feMdSBvU' limit 1 (0.38 ms)
* sqlite - select * from "users" where "id" = 1 limit 1 (0.05 ms)
* sqlite - select * from "categories" where "user_id" = 1 (0.15 ms)
* sqlite - select "name" from "categories" where (16 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 1) (0.07 ms)
* sqlite - select "name" from "categories" where (28 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 9) (0.04 ms)
* sqlite - select "name" from "categories" where (42 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 15) (0.04 ms)
* sqlite - select "name" from "categories" where (56 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 22) (0.03 ms)
* sqlite - select "name" from "categories" where (68 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 29) (0.04 ms)
* sqlite - select "name" from "categories" where (80 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 35) (0.03 ms)
* sqlite - select "name" from "categories" where (92 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 41) (0.04 ms)
* sqlite - select "name" from "categories" where (102 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 47) (0.04 ms)
* sqlite - select "name" from "categories" where (114 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 52) (0.05 ms)
* sqlite - select "name" from "categories" where (124 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 58) (0.04 ms)
* sqlite - select "name" from "categories" where (134 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 63) (0.06 ms)
* sqlite - select "name" from "categories" where (140 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 68) (0.06 ms)
* sqlite - select "name" from "categories" where (3 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 2) (0.04 ms)
* sqlite - select "name" from "categories" where (5 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 3) (0.05 ms)
* sqlite - select "name" from "categories" where (7 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 4) (0.04 ms)
* sqlite - select "name" from "categories" where (9 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 5) (0.04 ms)
* sqlite - select "name" from "categories" where (11 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 6) (0.04 ms)
* sqlite - select "name" from "categories" where (13 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 7) (0.03 ms)
* sqlite - select "name" from "categories" where (15 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 8) (0.04 ms)
* sqlite - select "name" from "categories" where (19 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 10) (0.03 ms)
* sqlite - select "name" from "categories" where (21 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 11) (0.04 ms)
* sqlite - select "name" from "categories" where (23 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 12) (0.03 ms)
* sqlite - select "name" from "categories" where (25 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 13) (0.04 ms)
* sqlite - select "name" from "categories" where (27 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 14) (0.03 ms)
* sqlite - select "name" from "categories" where (31 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 16) (0.04 ms)
* sqlite - select "name" from "categories" where (33 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 17) (0.03 ms)
* sqlite - select "name" from "categories" where (35 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 18) (0.03 ms)
* sqlite - select "name" from "categories" where (37 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 19) (0.04 ms)
* sqlite - select "name" from "categories" where (39 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 20) (0.03 ms)
* sqlite - select "name" from "categories" where (41 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 21) (0.04 ms)
* sqlite - select "name" from "categories" where (45 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 23) (0.03 ms)
* sqlite - select "name" from "categories" where (47 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 24) (0.04 ms)
* sqlite - select "name" from "categories" where (49 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 25) (0.04 ms)
* sqlite - select "name" from "categories" where (51 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 26) (0.04 ms)
* sqlite - select "name" from "categories" where (53 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 27) (0.04 ms)
* sqlite - select "name" from "categories" where (55 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 28) (0.08 ms)
* sqlite - select "name" from "categories" where (59 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 30) (0.05 ms)
* sqlite - select "name" from "categories" where (61 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 31) (0.04 ms)
* sqlite - select "name" from "categories" where (63 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 32) (0.04 ms)
* sqlite - select "name" from "categories" where (65 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 33) (0.04 ms)
* sqlite - select "name" from "categories" where (67 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 34) (0.06 ms)
* sqlite - select "name" from "categories" where (71 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 36) (0.09 ms)
* sqlite - select "name" from "categories" where (73 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 37) (0.08 ms)
* sqlite - select "name" from "categories" where (75 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 38) (0.06 ms)
* sqlite - select "name" from "categories" where (77 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 39) (0.04 ms)
* sqlite - select "name" from "categories" where (79 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 40) (0.04 ms)
* sqlite - select "name" from "categories" where (83 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 42) (0.04 ms)
* sqlite - select "name" from "categories" where (85 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 43) (0.04 ms)
* sqlite - select "name" from "categories" where (87 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 44) (0.06 ms)
* sqlite - select "name" from "categories" where (89 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 45) (0.07 ms)
* sqlite - select "name" from "categories" where (91 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 46) (0.06 ms)
* sqlite - select "name" from "categories" where (95 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 48) (0.05 ms)
* sqlite - select "name" from "categories" where (97 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 49) (0.04 ms)
* sqlite - select "name" from "categories" where (99 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 50) (0.04 ms)
* sqlite - select "name" from "categories" where (101 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 51) (0.04 ms)
* sqlite - select "name" from "categories" where (105 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 53) (0.04 ms)
* sqlite - select "name" from "categories" where (107 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 54) (0.05 ms)
* sqlite - select "name" from "categories" where (109 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 55) (0.04 ms)
* sqlite - select "name" from "categories" where (111 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 56) (0.04 ms)
* sqlite - select "name" from "categories" where (113 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 57) (0.05 ms)
* sqlite - select "name" from "categories" where (117 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 59) (0.05 ms)
* sqlite - select "name" from "categories" where (119 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 60) (0.05 ms)
* sqlite - select "name" from "categories" where (121 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 61) (0.05 ms)
* sqlite - select "name" from "categories" where (123 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 62) (0.06 ms)
* sqlite - select "name" from "categories" where (127 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 64) (0.04 ms)
* sqlite - select "name" from "categories" where (129 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 65) (0.04 ms)
* sqlite - select "name" from "categories" where (131 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 66) (0.04 ms)
* sqlite - select "name" from "categories" where (133 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 67) (0.04 ms)
* sqlite - select "name" from "categories" where (137 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 69) (0.03 ms)
* sqlite - select "name" from "categories" where (139 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 70) (0.03 ms)
* sqlite - select "name" from "categories" where (16 between "categories"."_lft" and "categories"."_rgt" and "categories"."id" <> 1) (0.04 ms)
<!--[if ENDBLOCK]><![endif]-->
