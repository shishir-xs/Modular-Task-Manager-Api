# Task Manager Admin - React Frontend

This directory contains the React-based admin interface for the Task Manager plugin.

## Development

### Prerequisites
- Node.js (v16 or higher)
- npm or yarn

### Installation

```bash
cd assets/admin
npm install
```

### Development Mode

Run the development server with hot reload:

```bash
npm run dev
```

This will watch for changes and rebuild automatically.

### Production Build

Build optimized production assets:

```bash
npm run build
```

The built files will be created in the `build/` directory.

## Project Structure

```
src/
├── components/          # React components
│   ├── TaskManager.jsx  # Main container component
│   ├── TaskList.jsx     # Task list table
│   ├── TaskItem.jsx     # Individual task row
│   └── TaskForm.jsx     # Task create/edit form
├── services/            # API services
│   └── api.js          # REST API functions
├── styles/             # CSS styles
│   └── main.css        # Main stylesheet
└── index.js            # Entry point

build/                  # Production build output (generated)
└── index.js           # Bundled JavaScript
└── index.css          # Bundled CSS
└── index.asset.php    # WordPress asset file
```

## Features

- ✅ Create new tasks
- ✅ Edit existing tasks
- ✅ Delete tasks
- ✅ View task list with status and priority badges
- ✅ Responsive design for mobile devices
- ✅ Integration with WordPress REST API
- ✅ Proper nonce handling for security

## WordPress Integration

The React app is loaded in the WordPress admin area at:
**Dashboard → Tasks**

The PHP Provider class handles:
- Admin menu registration
- Script and style enqueuing
- WordPress nonce for API security
- Localization of API URL and configuration
