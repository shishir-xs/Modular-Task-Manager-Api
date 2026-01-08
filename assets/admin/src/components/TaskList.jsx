import React from 'react';
import TaskItem from './TaskItem';

const TaskList = ({ tasks, onEdit, onDelete }) => {
    if (!tasks || tasks.length === 0) {
        return (
            <div className="task-list-empty">
                <p>No tasks found. Create your first task to get started!</p>
            </div>
        );
    }

    return (
        <div className="task-list">
            <table className="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th className="task-title-column">Title</th>
                        <th className="task-description-column">Description</th>
                        <th className="task-status-column">Status</th>
                        <th className="task-priority-column">Priority</th>
                        <th className="task-date-column">Due Date</th>
                        <th className="task-actions-column">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {tasks.map(task => (
                        <TaskItem
                            key={task.id}
                            task={task}
                            onEdit={onEdit}
                            onDelete={onDelete}
                        />
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default TaskList;
