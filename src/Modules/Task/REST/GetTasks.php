<?php

namespace TaskManager\Modules\Task\REST;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use TaskManager\Supports\Abstracts\AbstractREST;
use TaskManager\Modules\Task\Services\TaskService;

class GetTasks extends AbstractREST
{
    public static $loadable = true;
    public static string $route = '/tasks(?:/(?P<id>\d+))?';
    public static string $usableRoute = '/tasks';

    protected function getMethods(): string|array
    {
        return 'GET';
    }

    public function permissionCheck(WP_REST_Request $request): bool
    {
        return true; // Change to is_user_logged_in() if you want to restrict
    }

    public function handleRequest(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $id = $request->get_param('id');
        $status = $request->get_param('status');
        $priority = $request->get_param('priority');

        try {
            if ($id) {
                // Get single task
                $task = TaskService::getTaskById($id);
                if (!$task) {
                    return task_manager_rest_response(
                        data: null,
                        code: 404,
                        message: __('Task not found', 'modular-task-manager'),
                        headers: ['status' => 404]
                    );
                }

                return task_manager_rest_response(
                    data: $task,
                    code: 200,
                    message: __('Task retrieved successfully', 'modular-task-manager'),
                    headers: ['status' => 200]
                );
            } elseif ($status) {
                // Get tasks by status
                $tasks = TaskService::getTasksByStatus($status);
                return task_manager_rest_response(
                    data: $tasks,
                    code: 200,
                    message: __('Tasks retrieved successfully', 'modular-task-manager'),
                    headers: ['status' => 200]
                );
            } elseif ($priority) {
                // Get tasks by priority
                $tasks = TaskService::getTasksByPriority($priority);
                return task_manager_rest_response(
                    data: $tasks,
                    code: 200,
                    message: __('Tasks retrieved successfully', 'modular-task-manager'),
                    headers: ['status' => 200]
                );
            } else {
                // Get all tasks
                $tasks = TaskService::getAllTasks();
                return task_manager_rest_response(
                    data: $tasks,
                    code: 200,
                    message: __('Tasks retrieved successfully', 'modular-task-manager'),
                    headers: ['status' => 200]
                );
            }
        } catch (\Exception $e) {
            return task_manager_rest_response(
                data: null,
                code: 500,
                message: $e->getMessage(),
                headers: ['status' => 500]
            );
        }
    }
}
