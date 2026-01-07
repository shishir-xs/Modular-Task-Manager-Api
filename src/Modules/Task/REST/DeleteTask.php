<?php

namespace TaskManager\Modules\Task\REST;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use TaskManager\Supports\Abstracts\AbstractREST;
use TaskManager\Modules\Task\Services\TaskService;


class DeleteTask extends AbstractREST
{
    public static $loadable = true;
    public static string $route = '/tasks/(?P<id>\d+)';
    public static string $usableRoute = '/tasks/{id}';

    protected function getMethods(): string|array
    {
        return 'DELETE';
    }

    public function permissionCheck(WP_REST_Request $request): bool
    {
        return is_user_logged_in();
    }

    public function handleRequest(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $id = $request->get_param('id');

        try {
            // Check if task exists
            $task = TaskService::getTaskById($id);
            if (!$task) {
                return task_manager_rest_response(
                    data: null,
                    code: 404,
                    message: __('Task not found', 'modular-task-manager'),
                    headers: ['status' => 404]
                );
            }

            // Delete task
            $success = TaskService::deleteTask($id);
            if (!$success) {
                return task_manager_rest_response(
                    data: null,
                    code: 500,
                    message: __('Failed to delete task', 'modular-task-manager'),
                    headers: ['status' => 500]
                );
            }

            return task_manager_rest_response(
                data: ['id' => $id],
                code: 200,
                message: __('Task deleted successfully', 'modular-task-manager'),
                headers: ['status' => 200]
            );
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
