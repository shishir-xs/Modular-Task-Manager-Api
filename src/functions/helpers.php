<?php

/**
 * Helper function to get config instance
 *
 * @return \TaskManager\Supports\Config
 */
function task_manager_config()
{
    return \TaskManager\Supports\Config::instance();
}

/**
 * Helper function to create REST response
 *
 * @param mixed $data
 * @param int $code
 * @param string $message
 * @param array $headers
 * @return \WP_REST_Response
 */
function task_manager_rest_response($data = null, int $code = 200, string $message = '', array $headers = [])
{
    $response = [
        'success' => $code >= 200 && $code < 300,
        'data' => $data,
        'message' => $message,
    ];

    return new \WP_REST_Response($response, $code, $headers);
}
