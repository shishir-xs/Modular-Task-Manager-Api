<?php

namespace TaskManager\Supports;


class Config
{
    private static $instance = null;
    private $config = [];

    private function __construct()
    {
    }

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function add(string $key, $value): void
    {
        $this->config[$key] = $value;
    }

    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }


    public function has(string $key): bool
    {
        return isset($this->config[$key]);
    }

    public function all(): array
    {
        return $this->config;
    }
}
