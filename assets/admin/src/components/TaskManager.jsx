import React, { useState, useEffect } from 'react';
import TaskList from './TaskList';
import TaskForm from './TaskForm';
import { getTasks, saveTask, deleteTask } from '../services/api';

const TaskManager = () => {
    const [tasks, setTasks] = useState([]);
    const [loading, setLoading] = useState(true);
    const [editingTask, setEditingTask] = useState(null);
    const [showForm, setShowForm] = useState(false);
    const [error, setError] = useState(null);

    // Load tasks on mount
    useEffect(() => {
        loadTasks();
    }, []);

    const loadTasks = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await getTasks();
            
            if (response.success) {
                setTasks(response.data || []);
            } else {
                setError(response.message || 'Failed to load tasks');
            }
        } catch (err) {
            setError('Error loading tasks: ' + err.message);
            console.error('Error loading tasks:', err);
        } finally {
            setLoading(false);
        }
    };

    const handleSaveTask = async (taskData) => {
        try {
            setError(null);
            const response = await saveTask(taskData);
            
            if (response.success) {
                await loadTasks();
                setShowForm(false);
                setEditingTask(null);
            } else {
                setError(response.message || 'Failed to save task');
            }
        } catch (err) {
            setError('Error saving task: ' + err.message);
            console.error('Error saving task:', err);
        }
    };

    const handleDeleteTask = async (taskId) => {
        if (!confirm('Are you sure you want to delete this task?')) {
            return;
        }

        try {
            setError(null);
            const response = await deleteTask(taskId);
            
            if (response.success) {
                await loadTasks();
            } else {
                setError(response.message || 'Failed to delete task');
            }
        } catch (err) {
            setError('Error deleting task: ' + err.message);
            console.error('Error deleting task:', err);
        }
    };

    const handleEditTask = (task) => {
        setEditingTask(task);
        setShowForm(true);
    };

    const handleNewTask = () => {
        setEditingTask(null);
        setShowForm(true);
    };

    const handleCancelForm = () => {
        setShowForm(false);
        setEditingTask(null);
    };

    return (
        <div className="task-manager-container">
            <div className="task-manager-header">
                <h2>Task Management</h2>
                {!showForm && (
                    <button 
                        className="button button-primary" 
                        onClick={handleNewTask}
                    >
                        Add New Task
                    </button>
                )}
            </div>

            {error && (
                <div className="notice notice-error">
                    <p>{error}</p>
                </div>
            )}

            {showForm && (
                <TaskForm
                    task={editingTask}
                    onSave={handleSaveTask}
                    onCancel={handleCancelForm}
                />
            )}

            {loading ? (
                <div className="task-manager-loading">
                    <p>Loading tasks...</p>
                </div>
            ) : (
                <TaskList
                    tasks={tasks}
                    onEdit={handleEditTask}
                    onDelete={handleDeleteTask}
                />
            )}
        </div>
    );
};

export default TaskManager;
