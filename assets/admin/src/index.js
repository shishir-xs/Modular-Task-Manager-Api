import React from 'react';
import { createRoot } from 'react-dom/client';
import TaskManager from './components/TaskManager';
import './styles/main.css';

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', () => {
    const rootElement = document.getElementById('task-manager-root');
    
    if (rootElement) {
        const root = createRoot(rootElement);
        root.render(<TaskManager />);
    }
});
