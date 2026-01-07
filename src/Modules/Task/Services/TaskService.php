<?php

namespace TaskManager\Modules\Task\Services;

use TaskManager\Modules\Task\Data\TaskModel;


class TaskService
{
    public static function createTask(array $data): ?TaskModel
    {
        $data['status'] = $data['status'] ?? 'pending';
        $data['priority'] = $data['priority'] ?? 'medium';
        $data['created_by'] = $data['created_by'] ?? get_current_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $task = new TaskModel($data);
        return $task->save() ? $task : null;
    }

    public static function updateTask(int $taskId, array $data): bool
    {
        $task = TaskModel::find($taskId);
        if (!$task) {
            return false;
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        foreach ($data as $key => $value) {
            $task->$key = $value;
        }

        return $task->save();
    }

    public static function deleteTask(int $taskId): bool
    {
        $task = TaskModel::find($taskId);
        if (!$task) {
            return false;
        }
        return $task->delete();
    }

    public static function getAllTasks(): array
    {
        $tasks = TaskModel::all();
        return array_map(function($task) {
            return $task->toArray();
        }, $tasks);
    }

    public static function getTaskById(int $taskId): ?array
    {
        $task = TaskModel::find($taskId);
        return $task ? $task->toArray() : null;
    }

    public static function getTasksByStatus(string $status): array
    {
        $tasks = TaskModel::getByStatus($status);
        return array_map(function($task) {
            return $task->toArray();
        }, $tasks);
    }

    public static function getTasksByPriority(string $priority): array
    {
        $tasks = TaskModel::getByPriority($priority);
        return array_map(function($task) {
            return $task->toArray();
        }, $tasks);
    }

    public static function completeTask(int $taskId): bool
    {
        $task = TaskModel::find($taskId);
        if (!$task) {
            return false;
        }
        return $task->markAsCompleted();
    }

    public static function validateTaskData(array $data): ?array
    {
        $errors = [];

        // Validate title
        if (empty($data['title'])) {
            $errors[] = __('Title is required', 'modular-task-manager');
        } elseif (strlen($data['title']) > 255) {
            $errors[] = __('Title must not exceed 255 characters', 'modular-task-manager');
        }

        // Validate status
        if (!empty($data['status'])) {
            $valid_statuses = ['pending', 'in-progress', 'completed', 'cancelled'];
            if (!in_array($data['status'], $valid_statuses)) {
                $errors[] = __('Invalid status. Must be one of: pending, in-progress, completed, cancelled', 'modular-task-manager');
            }
        }

        // Validate priority
        if (!empty($data['priority'])) {
            $valid_priorities = ['low', 'medium', 'high', 'urgent'];
            if (!in_array($data['priority'], $valid_priorities)) {
                $errors[] = __('Invalid priority. Must be one of: low, medium, high, urgent', 'modular-task-manager');
            }
        }

        // Validate due_date format
        if (!empty($data['due_date'])) {
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $data['due_date']);
            if (!$date || $date->format('Y-m-d H:i:s') !== $data['due_date']) {
                // Try Y-m-d format
                $date = \DateTime::createFromFormat('Y-m-d', $data['due_date']);
                if (!$date || $date->format('Y-m-d') !== $data['due_date']) {
                    $errors[] = __('Due date must be in Y-m-d or Y-m-d H:i:s format', 'modular-task-manager');
                }
            }
        }

        return empty($errors) ? null : $errors;
    }
}
