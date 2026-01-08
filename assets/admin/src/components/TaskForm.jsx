import React, { useState, useEffect } from 'react';

const TaskForm = ({ task, onSave, onCancel }) => {
    const [formData, setFormData] = useState({
        title: '',
        description: '',
        status: 'pending',
        priority: 'medium',
        due_date: '',
    });

    const [errors, setErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        if (task) {
            setFormData({
                id: task.id,
                title: task.title || '',
                description: task.description || '',
                status: task.status || 'pending',
                priority: task.priority || 'medium',
                due_date: task.due_date || '',
            });
        }
    }, [task]);

    const validateForm = () => {
        const newErrors = {};

        if (!formData.title || formData.title.trim() === '') {
            newErrors.title = 'Title is required';
        }

        if (formData.title && formData.title.length > 255) {
            newErrors.title = 'Title must be less than 255 characters';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
        
        // Clear error for this field
        if (errors[name]) {
            setErrors(prev => ({
                ...prev,
                [name]: null
            }));
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        setSubmitting(true);
        try {
            await onSave(formData);
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="task-form-container">
            <div className="task-form-card">
                <h3>{task ? 'Edit Task' : 'New Task'}</h3>
                
                <form onSubmit={handleSubmit} className="task-form">
                    <div className="form-field">
                        <label htmlFor="task-title">
                            Title <span className="required">*</span>
                        </label>
                        <input
                            type="text"
                            id="task-title"
                            name="title"
                            value={formData.title}
                            onChange={handleChange}
                            className={`regular-text ${errors.title ? 'error' : ''}`}
                            disabled={submitting}
                        />
                        {errors.title && (
                            <p className="error-message">{errors.title}</p>
                        )}
                    </div>

                    <div className="form-field">
                        <label htmlFor="task-description">Description</label>
                        <textarea
                            id="task-description"
                            name="description"
                            value={formData.description}
                            onChange={handleChange}
                            rows="4"
                            className="large-text"
                            disabled={submitting}
                        />
                    </div>

                    <div className="form-row">
                        <div className="form-field">
                            <label htmlFor="task-status">Status</label>
                            <select
                                id="task-status"
                                name="status"
                                value={formData.status}
                                onChange={handleChange}
                                disabled={submitting}
                            >
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <div className="form-field">
                            <label htmlFor="task-priority">Priority</label>
                            <select
                                id="task-priority"
                                name="priority"
                                value={formData.priority}
                                onChange={handleChange}
                                disabled={submitting}
                            >
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>

                        <div className="form-field">
                            <label htmlFor="task-due-date">Due Date</label>
                            <input
                                type="date"
                                id="task-due-date"
                                name="due_date"
                                value={formData.due_date}
                                onChange={handleChange}
                                disabled={submitting}
                            />
                        </div>
                    </div>

                    <div className="form-actions">
                        <button
                            type="submit"
                            className="button button-primary"
                            disabled={submitting}
                        >
                            {submitting ? 'Saving...' : (task ? 'Update Task' : 'Create Task')}
                        </button>
                        <button
                            type="button"
                            className="button"
                            onClick={onCancel}
                            disabled={submitting}
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default TaskForm;
