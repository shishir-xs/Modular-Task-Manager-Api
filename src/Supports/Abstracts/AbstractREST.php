<?php

namespace TaskManager\Supports\Abstracts;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

abstract class AbstractREST
{
    public static $loadable = true;
    public static string $route = '';
    public static string $usableRoute = '';

    public function __construct()
    {
        if (!static::$loadable) {
            return;
        }

        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void
    {
        $namespace = 'task-manager/v1';
        $route = static::$route;
        
        register_rest_route($namespace, $route, [
            'methods' => $this->getMethods(),
            'callback' => [$this, 'handleRequest'],
            'permission_callback' => [$this, 'permissionCheck'],
        ]);
    }

    abstract protected function getMethods(): string|array;
    abstract public function handleRequest(WP_REST_Request $request): WP_REST_Response|WP_Error;
    abstract public function permissionCheck(WP_REST_Request $request): bool;
}
