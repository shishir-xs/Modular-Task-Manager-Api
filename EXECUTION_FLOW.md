# Modular Task Manager API - Execution Flow Documentation

## Overview
This document traces the complete execution flow from plugin initialization through API request handling, showing file-to-file and function-to-function execution paths.

---

## 1. Plugin Initialization Flow

### 1.1 Entry Point → Bootstrap
**File:** [`modular-task-manager-api.php`](modular-task-manager-api.php)

```
WordPress Plugin Activation
    ↓
📄 modular-task-manager-api.php (Lines 1-147)
    ↓
new ModularTaskManager() → __construct() (Line 147)
```

**Execution Steps:**

1. **Constructor Initialization** (Lines 34-47)
   ```
   ModularTaskManager::__construct()
       ├── Set static::$initiated = true (Line 42)
       ├── Set static::$pluginDir (Line 43)
       └── Register WordPress Hooks:
           ├── add_action('plugins_loaded', [$this, 'initiate'])
           ├── register_activation_hook(__FILE__, [$this, 'activatePlugin'])
           └── register_deactivation_hook(__FILE__, [$this, 'deactivatePlugin'])
   ```

2. **Configuration Setup** (Lines 49-65)
   ```
   ModularTaskManager::manageConfig()
       ├── $config = Config::instance()
       └── Add configurations:
           ├── plugin.name
           ├── plugin.version
           ├── plugin.path
           ├── plugin.url
           ├── plugin.public_url
           ├── plugin.public_path
           ├── plugin.text_domain
           ├── plugin.prefix
           └── plugin.file
   ```

3. **Function Loading** (Lines 67-72)
   ```
   ModularTaskManager::loadFunctions()
       └── Load src/functions/*.php
           └── 📄 helpers.php
               ├── task_manager_config()
               └── task_manager_rest_response()
   ```

4. **Plugin Initiation** (Lines 74-105)
   ```
   ModularTaskManager::initiate()
       ├── Call manageConfig()
       ├── Call loadFunctions()
       ├── Register SPL Autoloader
       │   └── Loads classes from TaskManager\ namespace
       └── new \TaskManager\Boot()
   ```

---

### 1.2 Boot → Module Loading
**File:** [`src/Boot.php`](src/Boot.php)

```
ModularTaskManager::initiate()
    ↓
📄 src/Boot.php (Lines 1-15)
    ↓
new Boot() → __construct()
```

**Execution Steps:**

```
Boot::__construct()
    ├── Add 'plugin.src_path' to config (Line 11)
    └── Load Task Module
        └── new Modules\Task\Provider()
```

---

### 1.3 Module Provider → Class Loading
**File:** [`src/Modules/Task/Provider.php`](src/Modules/Task/Provider.php)

```
Boot::__construct()
    ↓
📄 src/Modules/Task/Provider.php (Lines 1-16)
    ↓
new Provider() → __construct()
```

**Execution Steps:**

```
Provider::__construct() (extends AbstractLoader)
    └── Call classLoader() with directories:
        ├── plugin_dir_path(__FILE__) . 'Services'
        └── plugin_dir_path(__FILE__) . 'REST'
```

---

### 1.4 Abstract Loader → Class Auto-Loading
**File:** [`src/Supports/Abstracts/AbstractLoader.php`](src/Supports/Abstracts/AbstractLoader.php)

```
Provider::__construct()
    ↓
📄 src/Supports/Abstracts/AbstractLoader.php (Lines 1-31)
    ↓
AbstractLoader::classLoader()
```

**Execution Steps:**

```
AbstractLoader::classLoader(array $directories)
    └── For each directory:
        ├── Check if directory exists
        └── Load all *.php files with require_once
            ├── 📄 Services/TaskService.php
            ├── 📄 REST/GetTasks.php
            ├── 📄 REST/SaveTask.php
            └── 📄 REST/DeleteTask.php
```

---

### 1.5 REST Endpoint Registration
**Files:** REST endpoint classes in [`src/Modules/Task/REST/`](src/Modules/Task/REST/)

```
AbstractLoader::classLoader()
    ↓
Load REST Classes → __construct() of each
    ↓
AbstractREST::__construct()
```

**Execution Steps:**

For each REST class (GetTasks, SaveTask, DeleteTask):

```
ConcreteRESTClass::__construct() (extends AbstractREST)
    ├── Check static::$loadable (Line 23)
    └── add_action('rest_api_init', [$this, 'registerRoutes'])
        └── AbstractREST::registerRoutes()
            └── register_rest_route()
                ├── namespace: 'task-manager/v1'
                ├── route: static::$route
                ├── methods: getMethods()
                ├── callback: handleRequest()
                └── permission_callback: permissionCheck()
```

**Registered Endpoints:**

| Class | Route | Methods | Endpoint |
|-------|-------|---------|----------|
| GetTasks | `/tasks(?:/(?P<id>\d+))?` | GET | `/wp-json/task-manager/v1/tasks` |
| SaveTask | `/tasks(?:/(?P<id>\d+))?` | POST, PUT | `/wp-json/task-manager/v1/tasks` |
| DeleteTask | `/tasks/(?P<id>\d+)` | DELETE | `/wp-json/task-manager/v1/tasks/{id}` |

---

## 2. API Request Handling Flow

### 2.1 GET Request Flow (Retrieve Tasks)

```
HTTP GET /wp-json/task-manager/v1/tasks
    ↓
WordPress REST API Router
    ↓
📄 src/Modules/Task/REST/GetTasks.php
```

**Execution Steps:**

```
WordPress REST API
    ├── Call GetTasks::permissionCheck($request) (Lines 38-41)
    │   └── return true (public access)
    └── Call GetTasks::handleRequest($request) (Lines 49-111)
        ├── Extract parameters:
        │   ├── $id = $request->get_param('id')
        │   ├── $status = $request->get_param('status')
        │   └── $priority = $request->get_param('priority')
        └── Route to appropriate service method:
            ├── IF $id:
            │   └── TaskService::getTaskById($id)
            ├── ELSEIF $status:
            │   └── TaskService::getTasksByStatus($status)
            ├── ELSEIF $priority:
            │   └── TaskService::getTasksByPriority($priority)
            └── ELSE:
                └── TaskService::getAllTasks()
```

---

### 2.2 Service Layer → Data Layer (Get All Tasks)

**File:** [`src/Modules/Task/Services/TaskService.php`](src/Modules/Task/Services/TaskService.php)

```
GetTasks::handleRequest()
    ↓
📄 src/Modules/Task/Services/TaskService.php
    ↓
TaskService::getAllTasks()
```

**Execution Steps:**

```
TaskService::getAllTasks() (Lines 70-77)
    ├── Call TaskModel::all()
    │   └── 📄 src/Modules/Task/Data/TaskModel.php
    │       └── TaskModel::all()
    │           └── 📄 src/Supports/Abstracts/AbstractModel.php
    │               └── AbstractModel::all() (Lines 100-114)
    │                   ├── Query database: SELECT * FROM tasks
    │                   └── Instantiate TaskModel for each row
    └── Transform to array:
        └── array_map(fn($task) => $task->toArray(), $tasks)
```

---

### 2.3 POST Request Flow (Create Task)

```
HTTP POST /wp-json/task-manager/v1/tasks
    ↓
WordPress REST API Router
    ↓
📄 src/Modules/Task/REST/SaveTask.php
```

**Execution Steps:**

```
WordPress REST API
    ├── Call SaveTask::permissionCheck($request) (Lines 38-41)
    │   └── return is_user_logged_in()
    └── Call SaveTask::handleRequest($request) (Lines 49-126)
        ├── Extract $id = $request->get_param('id')
        ├── Extract $params = $request->get_params()
        ├── Validate data:
        │   └── TaskService::validateTaskData($params)
        └── Route based on $id:
            ├── IF $id exists:
            │   └── UPDATE flow (Lines 65-92)
            └── ELSE:
                └── CREATE flow (Lines 94-110)
```

---

### 2.4 Service Layer → Data Layer (Create Task)

**File:** [`src/Modules/Task/Services/TaskService.php`](src/Modules/Task/Services/TaskService.php)

```
SaveTask::handleRequest()
    ↓
📄 src/Modules/Task/Services/TaskService.php
    ↓
TaskService::createTask($data)
```

**Execution Steps:**

```
TaskService::createTask(array $data) (Lines 17-31)
    ├── Set default values:
    │   ├── $data['status'] = 'pending'
    │   ├── $data['priority'] = 'medium'
    │   ├── $data['created_by'] = get_current_user_id()
    │   ├── $data['created_at'] = date('Y-m-d H:i:s')
    │   └── $data['updated_at'] = date('Y-m-d H:i:s')
    ├── Create new TaskModel:
    │   └── $task = new TaskModel($data)
    │       └── 📄 src/Modules/Task/Data/TaskModel.php
    │           └── TaskModel::__construct($data)
    │               └── 📄 src/Supports/Abstracts/AbstractModel.php
    │                   └── AbstractModel::__construct($attributes) (Lines 18-25)
    │                       ├── Set global $wpdb
    │                       ├── Set $this->table = getTable()
    │                       └── Call fill($attributes)
    └── Save to database:
        └── $task->save()
            └── AbstractModel::save() (Lines 52-69)
                ├── Check if $this->id exists
                ├── IF $this->id:
                │   └── UPDATE query
                └── ELSE:
                    ├── INSERT query
                    └── Set $this->id = $wpdb->insert_id
```

---

### 2.5 PUT Request Flow (Update Task)

```
HTTP PUT /wp-json/task-manager/v1/tasks/5
    ↓
WordPress REST API Router
    ↓
📄 src/Modules/Task/REST/SaveTask.php
    ↓
SaveTask::handleRequest()
```

**Execution Steps:**

```
SaveTask::handleRequest() with $id = 5
    ├── Validate data: TaskService::validateTaskData($params)
    ├── Check task exists:
    │   └── TaskService::getTaskById($id)
    │       └── TaskModel::find($id)
    │           └── AbstractModel::find($id) (Lines 77-93)
    │               ├── Query: SELECT * FROM tasks WHERE id = %d
    │               └── Return TaskModel instance or null
    ├── Update task:
    │   └── TaskService::updateTask($id, $params) (Lines 38-52)
    │       ├── Find task: TaskModel::find($taskId)
    │       ├── Set $data['updated_at'] = now()
    │       ├── Update attributes: foreach ($data as $key => $value)
    │       └── Save: $task->save()
    │           └── AbstractModel::save() (Lines 52-69)
    │               └── wpdb->update($table, $attributes, ['id' => $this->id])
    └── Return updated task:
        └── TaskService::getTaskById($id)
```

---

### 2.6 DELETE Request Flow (Delete Task)

```
HTTP DELETE /wp-json/task-manager/v1/tasks/5
    ↓
WordPress REST API Router
    ↓
📄 src/Modules/Task/REST/DeleteTask.php
```

**Execution Steps:**

```
WordPress REST API
    ├── Call DeleteTask::permissionCheck($request) (Lines 36-39)
    │   └── return is_user_logged_in()
    └── Call DeleteTask::handleRequest($request) (Lines 47-80)
        ├── Extract $id = $request->get_param('id')
        ├── Check task exists:
        │   └── TaskService::getTaskById($id)
        └── Delete task:
            └── TaskService::deleteTask($id) (Lines 60-67)
                ├── Find task: TaskModel::find($taskId)
                └── Delete: $task->delete()
                    └── AbstractModel::delete() (Lines 123-128)
                        └── wpdb->delete($table, ['id' => $this->id])
```

---

## 3. Data Model Operations Flow

### 3.1 TaskModel Operations

**File:** [`src/Modules/Task/Data/TaskModel.php`](src/Modules/Task/Data/TaskModel.php)

```
TaskModel (extends AbstractModel)
    ├── Defines fillable attributes (Lines 16-27)
    └── Custom query methods:
        ├── getTable() → Returns table name
        ├── getByStatus($status) (Lines 38-57)
        ├── getByPriority($priority) (Lines 64-83)
        └── markAsCompleted() (Lines 90-95)
```

**Query Method Flow:**

```
TaskModel::getByStatus($status)
    ├── Get global $wpdb
    ├── Execute prepared query:
    │   └── SELECT * FROM {table} WHERE status = %s ORDER BY id DESC
    ├── Loop through results:
    │   ├── Create new TaskModel() for each row
    │   ├── Set $model->id
    │   └── Set $model->attributes
    └── Return array of TaskModel instances
```

---

### 3.2 AbstractModel Database Operations

**File:** [`src/Supports/Abstracts/AbstractModel.php`](src/Supports/Abstracts/AbstractModel.php)

```
AbstractModel (Lines 1-178)
    ├── Properties:
    │   ├── $wpdb (WordPress database object)
    │   ├── $table (table name)
    │   ├── $fillable (allowed attributes)
    │   ├── $attributes (current data)
    │   └── $id (record ID)
    └── Methods:
        ├── __construct($attributes) → Initialize model
        ├── fill($attributes) → Mass assignment (Lines 38-46)
        ├── save() → INSERT or UPDATE (Lines 52-69)
        ├── find($id) → SELECT by ID (Lines 77-93)
        ├── all() → SELECT all records (Lines 100-114)
        ├── delete() → DELETE record (Lines 123-128)
        ├── toArray() → Convert to array (Lines 150-153)
        └── Magic methods:
            ├── __get($key) → Get attribute
            └── __set($key, $value) → Set attribute
```

---

## 4. Helper Functions Flow

### 4.1 Configuration Helper

**File:** [`src/functions/helpers.php`](src/functions/helpers.php)

```
task_manager_config() (Lines 6-9)
    └── Returns Config::instance()
        └── 📄 src/Supports/Config.php
            └── Config::instance() (Lines 22-27)
                └── Returns singleton instance
```

**Usage:**
```php
task_manager_config()->get('plugin.version')
task_manager_config()->add('custom.key', 'value')
```

---

### 4.2 REST Response Helper

**File:** [`src/functions/helpers.php`](src/functions/helpers.php)

```
task_manager_rest_response($data, $code, $message, $headers) (Lines 18-30)
    ├── Build response array:
    │   ├── 'success' => ($code >= 200 && $code < 300)
    │   ├── 'data' => $data
    │   └── 'message' => $message
    └── Return new WP_REST_Response($response, $code, $headers)
```

**Usage in REST endpoints:**
```php
return task_manager_rest_response(
    data: $tasks,
    code: 200,
    message: 'Tasks retrieved successfully',
    headers: ['status' => 200]
);
```

---

## 5. Complete Request-Response Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│ HTTP Request: GET /wp-json/task-manager/v1/tasks               │
└────────────────────────┬────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│ WordPress REST API Router                                       │
│ - Matches route pattern                                        │
│ - Identifies handler: GetTasks                                 │
└────────────────────────┬────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│ GetTasks::permissionCheck()                                     │
│ - Check authorization (return true for public access)          │
└────────────────────────┬────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│ GetTasks::handleRequest($request)                               │
│ - Extract parameters                                           │
│ - Route to appropriate service                                 │
└────────────────────────┬────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│ TaskService::getAllTasks()                                      │
│ - Business logic layer                                         │
│ - Call data layer                                              │
└────────────────────────┬────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│ TaskModel::all()                                                │
│ - Extends AbstractModel                                        │
│ - Inherits database methods                                    │
└────────────────────────┬────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│ AbstractModel::all()                                            │
│ - Execute SQL: SELECT * FROM tasks ORDER BY id DESC           │
│ - Create TaskModel instances                                  │
└────────────────────────┬────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│ TaskService::getAllTasks() (continued)                          │
│ - Transform models to arrays                                   │
│ - Return data                                                  │
└────────────────────────┬────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│ GetTasks::handleRequest() (continued)                           │
│ - Call task_manager_rest_response()                            │
│ - Build standardized response                                  │
└────────────────────────┬────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│ task_manager_rest_response()                                    │
│ - Create response structure                                    │
│ - Return WP_REST_Response                                      │
└────────────────────────┬────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│ WordPress REST API Router                                       │
│ - Format JSON response                                         │
│ - Send HTTP response                                           │
└────────────────────────┬────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│ HTTP Response: JSON                                            │
│ {                                                              │
│   "success": true,                                             │
│   "data": [...tasks...],                                       │
│   "message": "Tasks retrieved successfully"                    │
│ }                                                              │
└─────────────────────────────────────────────────────────────────┘
```

---

## 6. Class Architecture Summary

```
┌─────────────────────────────────────────────────────────────────┐
│                    PLUGIN ARCHITECTURE                          │
└─────────────────────────────────────────────────────────────────┘

modular-task-manager-api.php
    ├── ModularTaskManager (Main plugin class)
    │   ├── manageConfig() → Initializes configuration
    │   ├── loadFunctions() → Loads helper functions
    │   ├── initiate() → Bootstraps the plugin
    │   └── activatePlugin() → Creates database tables
    │
    └── new \TaskManager\Boot()

src/Boot.php
    └── Boot::__construct()
        └── new Modules\Task\Provider()

src/Modules/Task/Provider.php (extends AbstractLoader)
    └── classLoader([Services, REST])

src/Supports/Abstracts/AbstractLoader.php
    └── classLoader() → Loads all classes in directories

src/Modules/Task/Services/TaskService.php
    ├── createTask($data)
    ├── updateTask($id, $data)
    ├── deleteTask($id)
    ├── getAllTasks()
    ├── getTaskById($id)
    ├── getTasksByStatus($status)
    ├── getTasksByPriority($priority)
    └── validateTaskData($data)

src/Modules/Task/REST/
    ├── GetTasks.php (extends AbstractREST)
    │   ├── getMethods() → 'GET'
    │   ├── permissionCheck() → true
    │   └── handleRequest() → Routes to TaskService
    │
    ├── SaveTask.php (extends AbstractREST)
    │   ├── getMethods() → ['POST', 'PUT']
    │   ├── permissionCheck() → is_user_logged_in()
    │   └── handleRequest() → Create or Update
    │
    └── DeleteTask.php (extends AbstractREST)
        ├── getMethods() → 'DELETE'
        ├── permissionCheck() → is_user_logged_in()
        └── handleRequest() → Delete task

src/Supports/Abstracts/AbstractREST.php
    ├── registerRoutes() → WordPress REST API registration
    ├── abstract getMethods()
    ├── abstract handleRequest()
    └── abstract permissionCheck()

src/Modules/Task/Data/TaskModel.php (extends AbstractModel)
    ├── $fillable → Allowed attributes
    ├── getTable() → 'wp_task_manager_tasks'
    ├── getByStatus($status)
    ├── getByPriority($priority)
    └── markAsCompleted()

src/Supports/Abstracts/AbstractModel.php
    ├── fill($attributes)
    ├── save() → INSERT/UPDATE
    ├── find($id) → SELECT by ID
    ├── all() → SELECT all
    ├── delete() → DELETE
    ├── toArray()
    └── Magic methods: __get(), __set()

src/Supports/Config.php
    ├── instance() → Singleton
    ├── add($key, $value)
    ├── get($key, $default)
    ├── has($key)
    └── all()

src/functions/helpers.php
    ├── task_manager_config()
    └── task_manager_rest_response()
```

---

## 7. Database Operations Flow

### 7.1 Create Operation
```
HTTP POST Request
    ↓
SaveTask::handleRequest()
    ↓
TaskService::createTask($data)
    ├── Set default values
    ├── new TaskModel($data)
    │   └── AbstractModel::__construct()
    │       └── fill($attributes)
    └── $task->save()
        └── AbstractModel::save()
            └── $wpdb->insert($table, $attributes)
                └── SQL: INSERT INTO wp_task_manager_tasks (title, ...) VALUES (...)
```

### 7.2 Read Operation
```
HTTP GET Request
    ↓
GetTasks::handleRequest()
    ↓
TaskService::getAllTasks()
    ↓
TaskModel::all()
    ↓
AbstractModel::all()
    └── $wpdb->get_results("SELECT * FROM {$table} ORDER BY id DESC")
        └── SQL: SELECT * FROM wp_task_manager_tasks ORDER BY id DESC
```

### 7.3 Update Operation
```
HTTP PUT Request
    ↓
SaveTask::handleRequest()
    ↓
TaskService::updateTask($id, $data)
    ├── TaskModel::find($id)
    │   └── AbstractModel::find($id)
    │       └── $wpdb->get_row("SELECT * FROM {$table} WHERE id = %d")
    ├── Update attributes
    └── $task->save()
        └── AbstractModel::save()
            └── $wpdb->update($table, $attributes, ['id' => $id])
                └── SQL: UPDATE wp_task_manager_tasks SET ... WHERE id = ?
```

### 7.4 Delete Operation
```
HTTP DELETE Request
    ↓
DeleteTask::handleRequest()
    ↓
TaskService::deleteTask($id)
    ├── TaskModel::find($id)
    └── $task->delete()
        └── AbstractModel::delete()
            └── $wpdb->delete($table, ['id' => $id])
                └── SQL: DELETE FROM wp_task_manager_tasks WHERE id = ?
```

---

## 8. Lifecycle Hooks Summary

### Plugin Activation
```
register_activation_hook(__FILE__, [$this, 'activatePlugin'])
    ↓
ModularTaskManager::activatePlugin()
    ├── Create database table: wp_task_manager_tasks
    │   └── dbDelta($sql)
    └── flush_rewrite_rules()
```

### Plugin Loading
```
add_action('plugins_loaded', [$this, 'initiate'])
    ↓
ModularTaskManager::initiate()
    ├── manageConfig()
    ├── loadFunctions()
    ├── Register autoloader
    └── new \TaskManager\Boot()
```

### REST API Registration
```
add_action('rest_api_init', [$this, 'registerRoutes'])
    ↓
AbstractREST::registerRoutes()
    └── register_rest_route($namespace, $route, $args)
```

### Plugin Deactivation
```
register_deactivation_hook(__FILE__, [$this, 'deactivatePlugin'])
    ↓
ModularTaskManager::deactivatePlugin()
    └── flush_rewrite_rules()
```

---

## 9. Key Design Patterns

### 9.1 Singleton Pattern
**Used in:** `Config` class
```php
Config::instance() → Returns single instance
```

### 9.2 Abstract Factory Pattern
**Used in:** `AbstractLoader`, `AbstractREST`, `AbstractModel`
- Base classes define interface
- Concrete classes implement specific behavior

### 9.3 Service Layer Pattern
**Used in:** `TaskService`
- Separates business logic from presentation
- Provides reusable methods

### 9.4 Active Record Pattern
**Used in:** `AbstractModel` and `TaskModel`
- Models represent database records
- Include database operations (save, delete, find)

### 9.5 Strategy Pattern
**Used in:** REST endpoint handlers
- Different strategies for GET, POST, PUT, DELETE
- Common interface through `AbstractREST`

---

## 10. Execution Timeline (Chronological)

```
Time: Plugin Load
├── 1. WordPress loads plugin file
├── 2. new ModularTaskManager()
├── 3. Register WordPress hooks
└── 4. Wait for 'plugins_loaded' action

Time: plugins_loaded Hook
├── 5. ModularTaskManager::initiate()
├── 6. Config::instance() created
├── 7. Load helper functions
├── 8. Register SPL autoloader
└── 9. new \TaskManager\Boot()

Time: Boot Construction
├── 10. Set src_path in config
└── 11. new Modules\Task\Provider()

Time: Provider Construction
├── 12. Call classLoader()
├── 13. Load all Service classes
│   └── TaskService.php loaded
└── 14. Load all REST classes
    ├── GetTasks.php loaded → new GetTasks()
    ├── SaveTask.php loaded → new SaveTask()
    └── DeleteTask.php loaded → new DeleteTask()

Time: REST Class Construction (for each)
├── 15. Check $loadable flag
└── 16. add_action('rest_api_init', registerRoutes)

Time: rest_api_init Hook
├── 17. GetTasks::registerRoutes()
├── 18. SaveTask::registerRoutes()
├── 19. DeleteTask::registerRoutes()
└── 20. All endpoints registered

Time: HTTP Request Received
├── 21. WordPress REST API routes request
├── 22. Call permission_callback
├── 23. Call main callback (handleRequest)
├── 24. Service layer processes request
├── 25. Model layer accesses database
├── 26. Return response up the chain
└── 27. JSON response sent to client
```

---

## 11. File Dependency Map

```
modular-task-manager-api.php
    ↓ requires
src/functions/helpers.php
    ↓ uses
src/Supports/Config.php

modular-task-manager-api.php
    ↓ instantiates
src/Boot.php
    ↓ instantiates
src/Modules/Task/Provider.php
    ↓ extends
src/Supports/Abstracts/AbstractLoader.php

src/Modules/Task/Provider.php
    ↓ loads
src/Modules/Task/Services/TaskService.php
    ↓ uses
src/Modules/Task/Data/TaskModel.php
    ↓ extends
src/Supports/Abstracts/AbstractModel.php

src/Modules/Task/Provider.php
    ↓ loads
src/Modules/Task/REST/*.php
    ↓ extends
src/Supports/Abstracts/AbstractREST.php
    ↓ uses
src/Modules/Task/Services/TaskService.php
```

---

## End of Documentation

This document provides a complete trace of the execution flow from plugin initialization through API request handling, showing all file-to-file transitions and function-to-function calls in the Modular Task Manager API plugin.
