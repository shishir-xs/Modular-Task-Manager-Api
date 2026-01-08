# REST API Routing Management Flow

## Overview
This document explains how REST API routing works in the Modular Task Manager plugin, focusing on `AbstractREST.php` and `GetTasks.php`.

---

## Simple Analogy: Restaurant Order System

Think of the REST API like a restaurant:

| Restaurant | REST API |
|------------|----------|
| **Menu** (list of dishes) | **Route Registry** (list of endpoints) |
| **Customer orders** "Burger" | **Browser requests** `/tasks` |
| **Waiter** checks if dish exists | **WordPress** checks if route exists |
| **Chef** prepares the food | **handleRequest()** processes the request |
| **Food delivered** to customer | **JSON response** sent to browser |

**Key Point:** Just like a restaurant creates a fresh menu every day (even if it's the same), WordPress rebuilds the route list on every page load.

---

## Visual Flow: Registration vs Execution

```
┌─────────────────────────────────────────────────────────────┐
│              EVERY PAGE LOAD (Registration)                 │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  1. Provider.php                                            │
│     └─> new GetTasks()                                      │
│                                                             │
│  2. AbstractREST.php (constructor)                          │
│     └─> add_action('rest_api_init', registerRoutes)         │
│                                                             │
│  3. WordPress fires 'rest_api_init' hook                    │
│                                                             │
│  4. AbstractREST.php (registerRoutes method)                │
│     └─> register_rest_route(...)  ← ROUTE REGISTERED        │
│                                                             │
│  Result: Route now in WordPress route registry (RAM)        │
│                                                             │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│         WHEN URL HIT (Execution - only on API call)         │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  User hits: GET /wp-json/task-manager/v1/tasks              │
│                                                             │
│  1. WordPress REST API (wp-includes/rest-api.php)           │
│     └─> Matches route in registry                           │
│                                                             │
│  2. GetTasks.php (permissionCheck)                          │
│     └─> Checks if user allowed  ← PERMISSION CHECK          │
│                                                             │
│  3. GetTasks.php (handleRequest)                            │
│     └─> Processes request  ← YOUR CODE EXECUTES             │
│     └─> TaskService.php called                              │
│     └─> Returns response                                    │
│                                                             │
│  Result: JSON sent to browser                               │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**Key Difference:**
- **Top box (Registration):** Happens on EVERY page load - prepares routes
- **Bottom box (Execution):** Happens ONLY when API URL is accessed - processes request

**Files Involved:**
- **Registration:** `AbstractREST.php` (registers the route)
- **Execution:** `GetTasks.php` (processes the request)

---

## Execution Flow: Browser to Response

### When User Hits: `http://yoursite.com/wp-json/task-manager/v1/tasks`

**Important:** Routes are registered **every time WordPress loads**, NOT just on API requests.

#### FIRST - Plugin Initialization (Every WordPress Page Load)
```
1. modular-task-manager-api.php
   └─> new ModularTaskManager()
       └─> __construct() - Registers hooks
       └─> initiate() - On 'plugins_loaded' hook
           ├─> Registers PSR-4 autoloader
           ├─> manageConfig()
           ├─> loadFunctions()
           └─> new Boot()

2. src/Boot.php
   └─> __construct()
       └─> new Modules\Task\Provider()

3. src/Modules/Task/Provider.php
   └─> __construct()
       └─> loadRESTClasses()
           └─> Loads REST/GetTasks.php
           └─> new GetTasks()

4. src/Modules/Task/REST/GetTasks.php
   └─> Extends AbstractREST
   └─> Calls parent::__construct()

5. src/Supports/Abstracts/AbstractREST.php
   └─> __construct()
       └─> add_action('rest_api_init', registerRoutes) ← HOOK REGISTERED
   
   (WordPress fires 'rest_api_init' - happens every page load)
   
   └─> registerRoutes() ← ROUTES REGISTERED HERE (EVERY TIME)
       └─> register_rest_route() - Route now available
```

**⚠️ Key Point:** 
- Routes register on **EVERY WordPress page load** (admin, frontend, API requests)
- Happens when `rest_api_init` hook fires
- NOT just when someone hits the API endpoint

**Why rebuild every time?**
Think of it like a restaurant menu board:
- Each morning, the chef writes today's menu on the board (even if same as yesterday)
- Customers can see what's available before ordering
- If a dish runs out, the chef can quickly erase it
- No need to print permanent menus (database) that might be outdated

**In WordPress:**
- Each page load, plugins register their routes (even if same as last time)
- WordPress knows what endpoints are available before requests come in
- If a plugin is deactivated, its routes automatically disappear
- No need to store in database and worry about stale data

#### THEN - On Each API Request (Only when hitting /wp-json/...)
```
User hits: GET /wp-json/task-manager/v1/tasks

6. WordPress REST API (wp-includes/rest-api.php)
   └─> Matches route: task-manager/v1/tasks
   └─> Finds registered GetTasks endpoint

7. src/Modules/Task/REST/GetTasks.php
   └─> permissionCheck()
       ├─> Returns true → Continue
       └─> Returns false → Stop (403 Error)

8. src/Modules/Task/REST/GetTasks.php
   └─> handleRequest()
       └─> $request->get_param('id')
       └─> TaskService::getAllTasks()

9. src/Modules/Task/Services/TaskService.php (Auto-loaded by PSR-4)
   └─> getAllTasks()
       └─> TaskModel::all()

10. src/Modules/Task/Data/TaskModel.php (Auto-loaded by PSR-4)
    └─> all() - Extends AbstractModel
    └─> Queries database
    └─> Returns task data

11. src/Modules/Task/Services/TaskService.php
    └─> Returns formatted tasks array

12. src/Modules/Task/REST/GetTasks.php
    └─> handleRequest()
        └─> task_manager_rest_response()

13. src/functions/helpers.php
    └─> task_manager_rest_response()
        └─> Returns WP_REST_Response

14. WordPress REST API
    └─> Sends JSON response to browser
```

#### LAST - Response Sent
```
Response: JSON data sent to browser
```

---

## Summary: First to Last File Execution

### Route Registration (EVERY WordPress page load)
| Order | File | Purpose | Frequency |
|-------|------|---------|-----------|
| 1st | `modular-task-manager-api.php` | Plugin entry point | Every page load |
| 2nd | `src/Boot.php` | Bootstrap plugin modules | Every page load |
| 3rd | `src/Modules/Task/Provider.php` | Load Task module | Every page load |
| 4th | `src/Supports/Abstracts/AbstractREST.php` | Register REST routes | Every page load |

**⚠️ Routes are re-registered every time WordPress loads (admin, frontend, API requests)**

### API Request Processing (Only when hitting /wp-json/...)
| Order | File | Purpose | Frequency |
|-------|------|---------|-----------|
| 1st | `src/Modules/Task/REST/GetTasks.php` | Check permissions | Per API request |
| 2nd | `src/Modules/Task/REST/GetTasks.php` | Handle request | Per API request |
| 3rd | `src/Modules/Task/Services/TaskService.php` | Business logic | Per API request |
| 4th | `src/Modules/Task/Data/TaskModel.php` | Database operations | Per API request |
| 5th | `src/functions/helpers.php` | Format response | Per API request |
| LAST | WordPress REST API | Send JSON to browser | Per API request |

---

## Route Registration FAQ

### Q: Are routes registered every time?
**A: Yes**, routes register on **every WordPress page load** when `rest_api_init` hook fires.

### Q: How does WordPress know all available routes before matching requests?

**A:** WordPress uses a **Route Registry Pattern**:

#### Important: "Memory" = RAM, NOT Database ⚠️

**Memory means:**
- ✅ PHP variables (RAM)
- ✅ Temporary, exists only during request
- ❌ NOT database
- ❌ NOT any wp_ table
- ❌ NOT stored permanently

**Simple Analogy:**
Think of RAM like a **whiteboard** and Database like a **filing cabinet**:

| Whiteboard (RAM) | Filing Cabinet (Database) |
|------------------|---------------------------|
| Write quickly | Takes time to open drawer |
| Erased after meeting | Saved permanently |
| Everyone sees current info | Needs to retrieve old info |
| **Routes stored here** | **Tasks stored here** |

**Why use whiteboard (RAM) for routes?**
- ⚡ Super fast access
- 🔄 Can change anytime
- 🧹 Auto-cleaned after each request
- 💡 No stale/outdated data

**Routes are stored in a PHP array in RAM:**
```php
// This is just a PHP variable in memory (RAM)
class WP_REST_Server {
    protected $routes = [];  // ← Just a PHP array, not database
}

// Like writing on a whiteboard:
$routes['task-manager/v1']['/tasks'] = [...];  // Quick! In RAM
// NOT like saving to filing cabinet:
// $wpdb->insert('wp_routes', [...]);  // Slow! In Database
```

#### Step 1: Build Route Registry (During Initialization)
```php
// WordPress internal route storage (simplified)
// This is a PHP array in RAM, cleared after each request
$routes = [];

// Your plugin registers:
register_rest_route('task-manager/v1', '/tasks', [...]);
// WordPress stores in RAM: $routes['task-manager/v1']['/tasks'] = [callback, methods, etc.]
// ⚠️ NOT saved to database - exists only during this request

// Other plugins register too:
register_rest_route('my-plugin/v1', '/posts', [...]);
// WordPress stores in RAM: $routes['my-plugin/v1']['/posts'] = [...]
```

**Result:** WordPress now has a complete map of all available routes **in RAM** (not database).

**In simple terms:** WordPress asks all plugins "what routes do you have?" at startup, stores them in a list, then uses that list to quickly match requests.

---

## What Data Goes Where?

Understanding what gets stored in RAM vs Database:

| Data Type | Storage | Reason | Example |
|-----------|---------|--------|---------|
| **Routes** | RAM (temporary) | Needs to be fast, changes often | `/tasks`, `/users` |
| **Tasks** | Database (permanent) | Needs to persist, user data | Task title, description |
| **Config** | RAM (temporary) | Only needed during request | Plugin paths, settings |
| **Posts** | Database (permanent) | Needs to persist, user content | Blog posts, pages |

**Cooking Analogy:**
- **RAM (whiteboard):** Recipe you're cooking today (temporary notes)
- **Database (cookbook):** Recipes you save forever (permanent storage)

**Routes = Recipe steps** you follow right now (whiteboard)  
**Tasks = Saved recipes** you keep forever (cookbook)

---

## Common Misconceptions

### ❌ Myth 1: "Routes are stored in database"
**Reality:** Routes are in RAM only, rebuilt every request.

### ❌ Myth 2: "Registering routes every time is slow"
**Reality:** Registration is extremely fast (~0.001 seconds), much faster than database lookup.

### ❌ Myth 3: "We should cache routes"
**Reality:** WordPress intentionally doesn't cache routes because plugins can be activated/deactivated.

### ❌ Myth 4: "Route registry persists across requests"
**Reality:** Registry is destroyed after each request, rebuilt from scratch next time.

---

#### Step 2: Match Incoming Request (When API Called)
```php
// User hits: GET /wp-json/task-manager/v1/tasks

// WordPress looks through $routes registry:
foreach ($routes as $namespace => $routes_in_namespace) {
    foreach ($routes_in_namespace as $route => $config) {
        if (matches($request_url, $route)) {
            // Found it! Execute callback
            call_user_func($config['callback'], $request);
        }
    }
}
```

#### Why This Design?

**Without Registry (Impossible):**
```
User hits: GET /wp-json/task-manager/v1/tasks
WordPress: "I don't know what plugins have what routes"
WordPress: "Should I load every plugin file and search?"
Result: ❌ Would be extremely slow
```

**Analogy:** Like a librarian searching every book in the library to find if a specific title exists (very slow!)

**With Registry (Current Design):**
```
1. On page load: All plugins register their routes (fast, in-memory)
2. User hits API: WordPress looks up route in registry (instant)
3. WordPress executes the matched callback
Result: ✅ Fast and efficient
```

**Analogy:** Like a librarian checking the catalog/index first (quick lookup!), then getting the specific book.

**Real-world comparison:**
- **Without Registry:** 30 seconds to search all files
- **With Registry:** 0.001 seconds to check array in RAM
- **Speed improvement:** 30,000x faster!

**In simple terms:**
Routes are like a **shopping list** you write on paper for each trip to the store:
- ✅ You recreate it each time (fresh and current)
- ✅ Quick to write (just list items)
- ✅ Easy to modify on the fly
- ❌ You don't save it in a filing cabinet (database)
- ❌ You don't carry last week's list (would be outdated)

### Q: Is this inefficient?
**A: No**, WordPress is designed this way:
- Registration is lightweight (just storing route info in RAM - not database)
- No database queries during registration
- Routes cleared after each request (not permanently stored)
- Alternative would be file scanning (much slower)
- Must rebuild route registry on every page load (by design)

### Q: Why not store routes in database?
**A:** Because:
1. **Performance** - Reading from RAM is 1000x faster than database
2. **Dynamic** - Plugins can be activated/deactivated, routes change
3. **Flexibility** - Different routes might be available based on context
4. **Simplicity** - No need to clear cache when plugins change

**Real-world analogy:**
Imagine a phone operator connecting calls:

**Database approach (slow):**
- Operator: "Please hold..."
- *Looks through paper phone book*
- *Finds the number*
- *Connects the call*
- **Takes:** 30 seconds per call

**RAM approach (fast):**
- Operator: *Has list of numbers in front of them*
- *Instantly finds number*
- *Connects the call*
- **Takes:** 1 second per call

**Result:** RAM is 30x faster! Same reason WordPress uses RAM for routes.

### Q: When does `rest_api_init` fire?
**A:** On every WordPress page load:
- Admin pages: ✅ Yes
- Frontend pages: ✅ Yes  
- API requests: ✅ Yes
- AJAX requests: ✅ Yes

### Q: When does `handleRequest()` run?
**A:** Only when someone actually hits the API endpoint:
- Viewing admin: ❌ No
- Viewing frontend: ❌ No
- GET /wp-json/task-manager/v1/tasks: ✅ Yes

---

## Route Matching Deep Dive

### Where Routes Are Actually Stored

**NOT in database:**
- ❌ NOT in `wp_options` table
- ❌ NOT in `wp_postmeta` table  
- ❌ NOT in any database table
- ❌ NOT in any cache

**In PHP memory (RAM):**
```php
// WordPress core file: wp-includes/rest-api/class-wp-rest-server.php
class WP_REST_Server {
    // This is just a PHP class property (RAM)
    protected $routes = [];  // ← Cleared after each request
    
    public function register_route($namespace, $route, $args) {
        // Stores in PHP array (RAM only)
        $this->routes[$namespace][$route] = $args;
    }
}
```

**Lifecycle:**
1. **Request starts** - `$routes = []` (empty array in RAM)
2. **Plugins load** - Routes registered into `$routes` array
3. **Request processed** - Routes used for matching
4. **Request ends** - `$routes` array destroyed (RAM cleared)
5. **Next request** - Start over from step 1

**Visual Analogy - Daily Whiteboard:**
```
Morning (Request Start):
   Clean whiteboard → Write today's routes → Use them → Erase at night

Next Morning (Next Request):
   Clean whiteboard → Write today's routes → Use them → Erase at night
   
   ↻ Repeat forever
```

**Why not keep yesterday's whiteboard?**
- Plugins might have been activated/deactivated
- Routes might have changed
- Better to have fresh, accurate information each time

**Memory Timeline:**
```
0ms    - Request starts, $routes = []
50ms   - Plugin 1 registers routes
75ms   - Plugin 2 registers routes
100ms  - Your plugin registers routes
200ms  - All routes ready in RAM
300ms  - API request processed
500ms  - Response sent
501ms  - REQUEST ENDS - $routes destroyed, RAM cleared 💥

0ms    - NEW Request starts, $routes = [] (fresh start)
```

### Internal WordPress Process

```php
// 1. Route Registration Phase
register_rest_route('task-manager/v1', '/tasks(?:/(?P<id>\d+))?', [
    'methods' => 'GET',
    'callback' => [GetTasks, 'handleRequest'],
]);

// WordPress stores internally:
WP_REST_Server::$routes['task-manager/v1']['/tasks(?:/(?P<id>\d+))?'] = [
    'methods' => ['GET'],
    'callback' => [GetTasks, 'handleRequest'],
    'permission_callback' => [GetTasks, 'permissionCheck'],
    // ... more config
];

// 2. Request Matching Phase
User requests: GET /wp-json/task-manager/v1/tasks/123

// WordPress REST API:
$server = rest_get_server();
$route_match = $server->match_request_to_handler($request);
// Loops through $routes, tests regex patterns
// Matches: '/tasks(?:/(?P<id>\d+))?' with '/tasks/123'
// Extracts: id=123

// 3. Execution Phase
$response = call_user_func($route_match['callback'], $request);
// Calls: GetTasks->handleRequest($request)
```

### Visual Api Working Flow

```
┌─────────────────────────────────────┐
│  INITIALIZATION (Every Page Load)   │
├─────────────────────────────────────┤
│ 1. rest_api_init hook fires         │
│ 2. Your plugin: register routes     │
│ 3. Other plugins: register routes   │
│ 4. WordPress: builds route registry │
│    ┌────────────────────────────┐   │
│    │ Route Registry (Memory)    │   │
│    │ - task-manager/v1/tasks    │   │
│    │ - my-plugin/v1/posts       │   │
│    │ - wp/v2/posts              │   │
│    └────────────────────────────┘   │
└─────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────┐
│    API REQUEST (When User Calls)    │
├─────────────────────────────────────┤
│ GET /wp-json/task-manager/v1/tasks  │
│                                     │
│ 1. WordPress checks registry        │
│ 2. Matches route pattern            │
│ 3. Calls permission_callback        │
│ 4. Calls handleRequest()            │
│ 5. Returns response                 │
└─────────────────────────────────────┘
```

**In simple terms:** WordPress builds a "phone book" of all routes when it starts up, so when a request comes in, it can quickly look up which "phone number" (callback) to call.

---

## Component Responsibilities

### 1. **AbstractREST.php** - Base Routing Manager

#### `__construct()` - Initialization
```php
public function __construct()
{
    if (!static::$loadable) {
        return;
    }
    add_action('rest_api_init', [$this, 'registerRoutes']);
}
```

**Responsibility:**
- Checks if route should be loaded (`$loadable`)
- Hooks `registerRoutes()` into WordPress `rest_api_init` action
- Called when child class is instantiated

---

#### `registerRoutes()` - Route Registration
```php
public function registerRoutes(): void
{
    $namespace = 'task-manager/v1';
    $route = static::$route;
    
    register_rest_route($namespace, $route, [
        'methods' => $this->getMethods(),
        'callback' => [$this, 'handleRequest'],
        'permission_callback' => [$this, 'permissionCheck'],
    ]);
}
```

**Responsibility:**
- Registers the route with WordPress REST API
- Defines the namespace: `task-manager/v1`
- Gets route pattern from child class (`static::$route`)
- Maps HTTP methods from `getMethods()`
- Sets request handler: `handleRequest()`
- Sets permission checker: `permissionCheck()`

---

#### Abstract Methods (Must be Implemented by Child)
```php
abstract protected function getMethods(): string|array;
abstract public function handleRequest(WP_REST_Request $request): WP_REST_Response|WP_Error;
abstract public function permissionCheck(WP_REST_Request $request): bool;
```

**Responsibility:**
- Forces child classes to implement routing logic
- `getMethods()` - Define HTTP methods (GET, POST, etc.)
- `handleRequest()` - Process the request
- `permissionCheck()` - Validate user access

---

### 2. **GetTasks.php** - Concrete Implementation

#### Static Properties - Route Configuration
```php
public static $loadable = true;
public static string $route = '/tasks(?:/(?P<id>\d+))?';
public static string $usableRoute = '/tasks';
```

**Responsibility:**
- `$loadable` - Enable/disable this endpoint
- `$route` - URL pattern with optional ID parameter
- `$usableRoute` - Human-readable route reference

**URL Mapping:**
- `/wp-json/task-manager/v1/tasks` → Get all tasks
- `/wp-json/task-manager/v1/tasks/123` → Get task with ID 123

---

#### `getMethods()` - HTTP Method Definition
```php
protected function getMethods(): string|array
{
    return 'GET';
}
```

**Responsibility:**
- Defines this endpoint accepts only GET requests
- Called by `registerRoutes()` in AbstractREST

---

#### `permissionCheck()` - Authorization
```php
public function permissionCheck(WP_REST_Request $request): bool
{
    return true; // Public access
}
```

**Responsibility:**
- Validates if requester has permission
- Called by WordPress before `handleRequest()`
- Returns `true` = allowed, `false` = denied (403 error)

**Examples:**
- `return true;` - Public access
- `return is_user_logged_in();` - Logged-in users only
- `return current_user_can('manage_options');` - Admins only

---

#### `handleRequest()` - Request Processing
```php
public function handleRequest(WP_REST_Request $request): WP_REST_Response|WP_Error
{
    $id = $request->get_param('id');
    $status = $request->get_param('status');
    $priority = $request->get_param('priority');
    
    // Process request and return response
}
```

**Responsibility:**
- Extracts parameters from request
- Processes business logic (via TaskService)
- Returns formatted response or error
- Called by WordPress when route is accessed

---

## Complete Flow Diagram

```
1. Plugin Initialization
   └─> Provider.php
       └─> loadRESTClasses()
           └─> new GetTasks()

2. GetTasks Instantiation
   └─> AbstractREST.__construct()
       └─> add_action('rest_api_init', registerRoutes)

3. WordPress Initialization
   └─> Fires 'rest_api_init' action
       └─> AbstractREST.registerRoutes()
           └─> register_rest_route(
                   namespace: 'task-manager/v1',
                   route: '/tasks(?:/(?P<id>\d+))?',
                   methods: 'GET',
                   callback: handleRequest,
                   permission_callback: permissionCheck
               )

4. API Request Received
   GET /wp-json/task-manager/v1/tasks
   └─> WordPress REST API
       └─> GetTasks.permissionCheck()
           ├─> Returns true → Continue
           └─> Returns false → 403 Error
       └─> GetTasks.handleRequest()
           └─> TaskService.getAllTasks()
           └─> Return WP_REST_Response

5. Response Sent
   └─> JSON response to client
```

---

## Function Call Sequence

### Initialization Phase (Once)
```
1. new GetTasks()
2. AbstractREST.__construct()
3. add_action('rest_api_init', registerRoutes)
4. WordPress fires 'rest_api_init'
5. AbstractREST.registerRoutes()
6. register_rest_route()
```

### Request Phase (Every API Call)
```
1. Client sends GET request
2. WordPress matches route
3. GetTasks.permissionCheck() → Authorization
4. GetTasks.handleRequest() → Processing
5. Return WP_REST_Response → JSON response
```

---

## Key Functions Summary

| Function | File | Purpose | When Called |
|----------|------|---------|-------------|
| `__construct()` | AbstractREST | Hook into WordPress REST API | On class instantiation |
| `registerRoutes()` | AbstractREST | Register route with WordPress | On `rest_api_init` hook |
| `getMethods()` | GetTasks | Define HTTP methods | During route registration |
| `permissionCheck()` | GetTasks | Validate user access | Before each request |
| `handleRequest()` | GetTasks | Process API request | On each valid request |

---

## Route Registration Details

### Registered Route
- **Namespace:** `task-manager/v1`
- **Route Pattern:** `/tasks(?:/(?P<id>\d+))?`
- **Full URL:** `http://yoursite.com/wp-json/task-manager/v1/tasks`

### URL Variations
| URL | Description | Parameter |
|-----|-------------|-----------|
| `/tasks` | Get all tasks | None |
| `/tasks?status=pending` | Filter by status | `status=pending` |
| `/tasks?priority=high` | Filter by priority | `priority=high` |
| `/tasks/123` | Get specific task | `id=123` |

---

## How to Add New Endpoints

1. **Create new class** extending `AbstractREST`
2. **Define route** via static properties
3. **Implement required methods:**
   - `getMethods()` - HTTP method
   - `permissionCheck()` - Authorization logic
   - `handleRequest()` - Business logic
4. **Place in REST folder** - Auto-loaded by Provider

Example:
```php
class DeleteTask extends AbstractREST
{
    public static string $route = '/tasks/(?P<id>\d+)';
    
    protected function getMethods(): string { return 'DELETE'; }
    public function permissionCheck(WP_REST_Request $request): bool { 
        return is_user_logged_in(); 
    }
    public function handleRequest(WP_REST_Request $request): WP_REST_Response {
        // Delete logic
    }
}
```

---

## Conclusion

**Routing Management Flow:**
1. `AbstractREST.__construct()` - Hooks into WordPress
2. `AbstractREST.registerRoutes()` - Registers the route
3. `GetTasks.getMethods()` - Defines HTTP methods
4. `GetTasks.permissionCheck()` - Validates access
5. `GetTasks.handleRequest()` - Processes requests

This architecture follows the **Template Method Pattern**, where AbstractREST provides the routing framework, and GetTasks implements the specific endpoint behavior.
