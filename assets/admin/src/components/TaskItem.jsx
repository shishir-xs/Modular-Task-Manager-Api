import React from 'react';

const TaskItem = ({ task, onEdit, onDelete }) => {
    const getStatusBadge = (status) => {
        const statusClasses = {
            'pending': 'task-status-badge task-status-pending',
            'in_progress': 'task-status-badge task-status-in-progress',
            'completed': 'task-status-badge task-status-completed',
        };
        
        const statusLabels = {
            'pending': 'Pending',
            'in_progress': 'In Progress',
            'completed': 'Completed',
        };

        return (
            <span className={statusClasses[status] || 'task-status-badge'}>
                {statusLabels[status] || status}
            </span>
        );
    };

    const getPriorityBadge = (priority) => {
        const priorityClasses = {
            'low': 'task-priority-badge task-priority-low',
            'medium': 'task-priority-badge task-priority-medium',
            'high': 'task-priority-badge task-priority-high',
        };

        return (
            <span className={priorityClasses[priority] || 'task-priority-badge'}>
                {priority ? priority.charAt(0).toUpperCase() + priority.slice(1) : 'N/A'}
            </span>
        );
    };

    const formatDate = (dateString) => {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString();
    };

    return (
        <tr>
            <td className="task-title">
                <strong>{task.title}</strong>
            </td>
            <td className="task-description">
                {task.description ? (
                    <div className="task-description-text">
                        {task.description.length > 100 
                            ? `${task.description.substring(0, 100)}...` 
                            : task.description
                        }
                    </div>
                ) : (
                    <em>No description</em>
                )}
            </td>
            <td className="task-status">
                {getStatusBadge(task.status)}
            </td>
            <td className="task-priority">
                {getPriorityBadge(task.priority)}
            </td>
            <td className="task-due-date">
                {formatDate(task.due_date)}
            </td>
            <td className="task-actions">
                <button
                    className="button button-small"
                    onClick={() => onEdit(task)}
                    title="Edit task"
                >
                    Edit
                </button>
                <button
                    className="button button-small button-link-delete"
                    onClick={() => onDelete(task.id)}
                    title="Delete task"
                >
                    Delete
                </button>
            </td>
        </tr>
    );
};

export default TaskItem;
