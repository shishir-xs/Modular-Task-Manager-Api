# 📋 Modular Task Manager API

A WordPress plugin with modular architecture for task management featuring REST API **and React Admin Interface**.

## 🏗️ Architecture

This plugin follows the **booking-engine** plugin's modular architecture pattern:

```
modular-task-manager-api/
├── modular-task-manager-api.php    # Main plugin file
├── assets/
│   └── admin/                       # React admin frontend
│       ├── src/                     # Source files
│       │   ├── components/         # React components
│       │   ├── services/           # API services
│       │   └── styles/             # CSS styles
│       ├── build/                  # Built assets
│       ├── package.json            # Dependencies
│       └── webpack.config.js       # Build config
└── src/
    ├── Boot.php                     # Application bootstrap
    ├── Supports/                    # Support classes
    │   ├── Config.php              # Configuration management
    │   └── Abstracts/              # Abstract base classes
    │       ├── AbstractLoader.php  # Class loader
    │       ├── AbstractREST.php    # REST base class
    │       └── AbstractModel.php   # Model base class
    ├── functions/
    │   └── helpers.php             # Helper functions
    └── Modules/
        ├── Admin/                  # Admin UI module
        │   ├── Provider.php        # Admin menu & assets
        │   └── views/
        │       └── admin-page.php  # Admin template
        └── Task/                   # Task module
            ├── Provider.php        # Module provider
            ├── Data/
            │   └── TaskModel.php   # Database model
            ├── Services/
            │   └── TaskService.php # Business logic
            └── REST/               # API endpoints
                ├── GetTasks.php    # GET endpoints
                ├── SaveTask.php    # POST/PUT endpoints
                └── DeleteTask.php  # DELETE endpoint
```

## 🚀 Features

### Backend (REST API)
- ✅ **Modular Architecture** - Following booking-engine pattern
- ✅ **Complete CRUD Operations** - Create, Read, Update, Delete
- ✅ **REST API** - WordPress REST API integration
- ✅ **Task Management** - Title, description, status, priority, due date
- ✅ **Status Tracking** - pending, in-progress, completed, cancelled
- ✅ **Priority Levels** - low, medium, high, urgent
- ✅ **User Authentication** - Secure endpoints
- ✅ **Data Validation** - Input validation and sanitization

### Frontend (React Admin UI)
- ✅ **React-based Interface** - Modern, responsive UI
- ✅ **WordPress Integration** - Seamless admin menu integration
- ✅ **CRUD Interface** - Create, edit, delete tasks from admin panel
- ✅ **Real-time Updates** - Instant UI updates after operations
- ✅ **Form Validation** - Client-side validation with error messages
- ✅ **Status & Priority Badges** - Color-coded visual indicators
- ✅ **Mobile Responsive** - Works on all devices
- ✅ **WordPress Styling** - Consistent with WordPress admin design

## 📊 Database Schema

**Table:** `wp_task_manager_tasks`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint(20) | Primary key |
| title | varchar(255) | Task title |
| description | text | Task description |
| status | varchar(50) | pending/in-progress/completed/cancelled |
| priority | varchar(50) | low/medium/high/urgent |
| due_date | datetime | Due date |
| completed_at | datetime | Completion timestamp |
| created_by | bigint(20) | User ID |
| created_at | datetime | Creation timestamp |
| updated_at | datetime | Last update timestamp |

## 🌐 API Endpoints

### Base URL
```
http://localhost/wp-atlas/wp-json/task-manager/v1
```

### 1. **GET** - Get All Tasks
```
GET /tasks

Response:
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Complete project documentation",
            "description": "Write comprehensive documentation",
            "status": "pending",
            "priority": "high",
            "due_date": "2026-01-15 00:00:00",
            "completed_at": null,
            "created_by": 1,
            "created_at": "2026-01-07 10:00:00",
            "updated_at": "2026-01-07 10:00:00"
        }
    ],
    "message": "Tasks retrieved successfully"
}
```

### 2. **GET** - Get Single Task
```
GET /tasks/{id}

Example: GET /tasks/1
```

### 3. **GET** - Filter by Status
```
GET /tasks?status=pending
GET /tasks?status=in-progress
GET /tasks?status=completed
GET /tasks?status=cancelled
```

### 4. **GET** - Filter by Priority
```
GET /tasks?priority=low
GET /tasks?priority=medium
GET /tasks?priority=high
GET /tasks?priority=urgent
```

### 5. **POST** - Create Task (Authentication Required)
```
POST /tasks

Headers:
Authorization: Basic {base64(username:app_password)}

Body (JSON):
{
    "title": "New Task",
    "description": "Task description here",
    "status": "pending",
    "priority": "medium",
    "due_date": "2026-01-15"
}

Response (201):
{
    "success": true,
    "data": {
        "id": 1,
        "title": "New Task",
        "description": "Task description here",
        "status": "pending",
        "priority": "medium",
        "due_date": "2026-01-15",
        "completed_at": null,
        "created_by": 1,
        "created_at": "2026-01-07 10:00:00",
        "updated_at": "2026-01-07 10:00:00"
    },
    "message": "Task created successfully"
}
```

### 6. **PUT** - Update Task (Authentication Required)
```
PUT /tasks/{id}

Headers:
Authorization: Basic {base64(username:app_password)}

Body (JSON):
{
    "title": "Updated Task Title",
    "status": "in-progress"
}

Response (200):
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Updated Task Title",
        "status": "in-progress",
        ...
    },
    "message": "Task updated successfully"
}
```

### 7. **DELETE** - Delete Task (Authentication Required)
```
DELETE /tasks/{id}

Headers:
Authorization: Basic {base64(username:app_password)}

Response (200):
{
    "success": true,
    "data": {
        "id": 1
    },
    "message": "Task deleted successfully"
}
```

## 🔐 Authentication

Create/Update/Delete operations require WordPress authentication:

### Using Application Password (Recommended):

1. Go to WordPress Admin → Users → Profile
2. Scroll to "Application Passwords"
3. Create new application password
4. Use in Postman with Basic Auth:
   - **Username:** your-wordpress-username
   - **Password:** generated-application-password

## 🧪 Testing with Postman

### Step 1: GET All Tasks (No Auth)
```
Method: GET
URL: http://localhost/wp-atlas/wp-json/task-manager/v1/tasks
```

### Step 2: CREATE Task (With Auth)
```
Method: POST
URL: http://localhost/wp-atlas/wp-json/task-manager/v1/tasks

Authorization:
Type: Basic Auth
Username: admin
Password: xxxx xxxx xxxx xxxx (Application Password)

Headers:
Content-Type: application/json

Body (raw JSON):
{
    "title": "Test Task",
    "description": "This is a test task",
    "status": "pending",
    "priority": "high",
    "due_date": "2026-01-15"
}
```

### Step 3: UPDATE Task
```
Method: PUT
URL: http://localhost/wp-atlas/wp-json/task-manager/v1/tasks/1

Body:
{
    "status": "completed"
}
```

### Step 4: DELETE Task
```
Method: DELETE
URL: http://localhost/wp-atlas/wp-json/task-manager/v1/tasks/1
```

## ✅ Validation Rules

### Required Fields:
- **title** (max 255 characters)

### Optional Fields:
- **description** (text)
- **status** (pending/in-progress/completed/cancelled)
- **priority** (low/medium/high/urgent)
- **due_date** (Y-m-d or Y-m-d H:i:s format)

### Error Response Example:
```json
{
    "success": false,
    "data": null,
    "message": "Title is required"
}
```

## 📦 Installation

### Backend Setup

1. Upload plugin to `wp-content/plugins/modular-task-manager-api/`
2. Activate plugin from WordPress Admin → Plugins
3. Database table will be created automatically
4. API endpoints will be available immediately

### Frontend Setup (Admin UI)

See **[QUICK_START_ADMIN.md](QUICK_START_ADMIN.md)** for quick setup, or **[ADMIN_FRONTEND_SETUP.md](ADMIN_FRONTEND_SETUP.md)** for detailed guide.

**Quick Steps:**
```bash
cd wp-content/plugins/modular-task-manager-api/assets/admin
npm install
npm run build
```

Then access **Dashboard → Tasks** menu in WordPress admin.

## 🖥️ Using the Admin Interface

1. **Access**: WordPress Admin → **Tasks** menu (left sidebar)
2. **Create Task**: Click "Add New Task" button
3. **Edit Task**: Click "Edit" on any task row
4. **Delete Task**: Click "Delete" on any task (with confirmation)
5. **View Tasks**: All tasks displayed in responsive table

**Features:**
- Form validation with error messages
- Color-coded status badges (Pending, In Progress, Completed)
- Priority indicators (Low, Medium, High)
- Date picker for due dates
- Mobile-responsive design

## 🛠️ Technology Stack

- **PHP 8.0+**
- **WordPress 6.0+**
- **WordPress REST API**
- **MySQL Database**
- **Modular Architecture**

## 📚 Code Structure

### Modular Pattern:
- **Provider** - Registers module and loads classes
- **Model** - Database operations (extends AbstractModel)
- **Service** - Business logic and validation
- **REST** - API endpoints (extends AbstractREST)

### Support Classes:
- **Config** - Configuration management singleton
- **AbstractLoader** - Automatic class loading
- **AbstractREST** - Base REST controller
- **AbstractModel** - Base database model

## 🎯 Status Values
- `pending` - Task not started
- `in-progress` - Task is being worked on
- `completed` - Task finished
- `cancelled` - Task cancelled

## 📈 Priority Values
- `low` - Low priority
- `medium` - Medium priority (default)
- `high` - High priority
- `urgent` - Urgent priority

## 🔄 Updates

To add new features:

1. Create new module in `src/Modules/`
2. Add Provider class
3. Create Data/Model
4. Create Services for business logic
5. Create REST endpoints
6. Register in `src/Boot.php`

## 📄 License

GPL v2 or later

## 👨‍💻 Author

Built following booking-engine plugin architecture

---

**Happy Task Managing! 🚀**
