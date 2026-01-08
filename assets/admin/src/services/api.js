/**
 * API service for Task Manager
 */

const { apiUrl, nonce } = window.taskManagerData || {};

/**
 * Fetch helper with error handling
 */
const apiFetch = async (endpoint, options = {}) => {
    const url = `${apiUrl}${endpoint}`;
    
    const headers = {
        'Content-Type': 'application/json',
        'X-WP-Nonce': nonce,
        ...options.headers,
    };

    const response = await fetch(url, {
        ...options,
        headers,
    });

    if (!response.ok) {
        const error = await response.json().catch(() => ({
            message: 'Network response was not ok'
        }));
        throw new Error(error.message || 'API request failed');
    }

    return response.json();
};

/**
 * Get all tasks
 */
export const getTasks = async (params = {}) => {
    const queryString = new URLSearchParams(params).toString();
    const endpoint = `/tasks${queryString ? `?${queryString}` : ''}`;
    
    return apiFetch(endpoint, {
        method: 'GET',
    });
};

/**
 * Save task (create or update)
 */
export const saveTask = async (taskData) => {
    return apiFetch('/tasks', {
        method: 'POST',
        body: JSON.stringify(taskData),
    });
};

/**
 * Delete task
 */
export const deleteTask = async (taskId) => {
    return apiFetch(`/tasks/${taskId}`, {
        method: 'DELETE',
    });
};
