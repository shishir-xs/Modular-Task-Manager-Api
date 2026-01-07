<?php

namespace TaskManager\Modules\Task\REST;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use TaskManager\Supports\Abstracts\AbstractREST;
use TaskManager\Modules\Task\Services\TaskService;

class SaveTask extends AbstractREST
{
    public static $loadable = true;
    public static string $route = '/tasks(?:/(?P<id>\d+))?';
    public static string $usableRoute = '/tasks';

    protected function getMethods(): string|array
    {
        return ['POST', 'PUT'];
    }

    public function permissionCheck(WP_REST_Request $request): bool
    {
        return is_user_logged_in();
    }

    public function handleRequest(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $id = $request->get_param('id');
        $params = $request->get_params();

        try {
            $errors = TaskService::validateTaskData($params);
            if ($errors) {
                return task_manager_rest_response(
                    data: null,
                    code: 400,
                    message: implode(', ', $errors),
                    headers: ['status' => 400]
                );
            }

            if ($id) {
                // Update existing task
                $task = TaskService::getTaskById($id);
                if (!$task) {
                    return task_manager_rest_response(
                        data: null,
                        code: 404,
                        message: __('Task not found', 'modular-task-manager'),
                        headers: ['status' => 404]
                    );
                }

                $success = TaskService::updateTask($id, $params);
                if (!$success) {
                    return task_manager_rest_response(
                        data: null,
                        code: 500,
                        message: __('Failed to update task', 'modular-task-manager'),
                        headers: ['status' => 500]
                    );
                }

                $updatedTask = TaskService::getTaskById($id);
                return task_manager_rest_response(
                    data: $updatedTask,
                    code: 200,
                    message: __('Task updated successfully', 'modular-task-manager'),
                    headers: ['status' => 200]
                );
            } else {
                // Create new task
                $task = TaskService::createTask($params);
                if (!$task) {
                    return task_manager_rest_response(
                        data: null,
                        code: 500,
                        message: __('Failed to create task', 'modular-task-manager'),
                        headers: ['status' => 500]
                    );
                }

                return task_manager_rest_response(
                    data: $task->toArray(),
                    code: 201,
                    message: __('Task created successfully', 'modular-task-manager'),
                    headers: ['status' => 201]
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
