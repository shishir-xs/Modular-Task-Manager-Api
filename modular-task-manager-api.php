<?php

defined('ABSPATH') || exit;

/**
 * Plugin Name: Modular Task Manager API
 * Description: A modular WordPress plugin for task management with REST API support
 * Plugin URI: #
 * Author: Your Name
 * Version: 1.0.0
 * Author URI: #
 *
 * Text Domain: modular-task-manager
 * Domain Path: /languages
 *
 */

if (!defined('TASK_MANAGER_VERSION')) {
    define('TASK_MANAGER_VERSION', '1.0.0');
}

if (!defined('TASK_MANAGER_PREFIX')) {
    define('TASK_MANAGER_PREFIX', 'task_manager');
}

if (!defined('TASK_MANAGER_DB_PREFIX')) {
    global $wpdb;
    define('TASK_MANAGER_DB_PREFIX', $wpdb->prefix);
}

final class ModularTaskManager
{
    protected static $pluginDir;
    protected static $initiated;

    public function __construct()
    {
        if (self::$initiated === true) {
            return;
        }
        self::$initiated = true;
        self::$pluginDir = plugin_dir_path(__FILE__);

        add_action('plugins_loaded', [$this, 'initiate'], 10);
        register_activation_hook(__FILE__, [$this, 'activatePlugin']);
        register_deactivation_hook(__FILE__, [$this, 'deactivatePlugin']);
    }

    public static function manageConfig()
    {
        $config = \TaskManager\Supports\Config::instance();

        $config->add('plugin.name', 'Modular Task Manager');
        $config->add('plugin.version', TASK_MANAGER_VERSION);
        $config->add('plugin.path', trailingslashit(plugin_dir_path(__FILE__)));
        $config->add('plugin.url', trailingslashit(plugin_dir_url(__FILE__))); // ✅ Fixed: Removed 'key:' named argument
        $config->add('plugin.public_url', trailingslashit(plugin_dir_url(__FILE__)) . 'public/');
        $config->add('plugin.public_path', trailingslashit(plugin_dir_path(__FILE__)) . 'public/');
        $config->add('plugin.text_domain', 'modular-task-manager');
        $config->add('plugin.prefix', TASK_MANAGER_PREFIX);
        $config->add('plugin.file', __FILE__);
    }

    public static function loadFunctions()
    {
        // Include all files from functions directory: src/functions/*.php
        foreach (glob(self::$pluginDir . 'src/functions/*.php') as $file) {
            include_once $file;
        }
    }

    public function initiate()
    {
        self::manageConfig();
        self::loadFunctions();

        // Autoload classes
        spl_autoload_register(function ($class) {
            $prefix = 'TaskManager\\';
            $base_dir = self::$pluginDir . 'src/';

            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relative_class = substr($class, $len);
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

            if (file_exists($file)) {
                require $file;
            }
        });

        new \TaskManager\Boot();
    }

    public function activatePlugin()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Create tasks table
        $tasks_table = TASK_MANAGER_DB_PREFIX . 'task_manager_tasks';

        $sql = "CREATE TABLE IF NOT EXISTS $tasks_table (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			title varchar(255) NOT NULL,
			description text DEFAULT NULL,
			status varchar(50) NOT NULL DEFAULT 'pending',
			priority varchar(50) NOT NULL DEFAULT 'medium',
			due_date datetime DEFAULT NULL,
			completed_at datetime DEFAULT NULL,
			created_by bigint(20) UNSIGNED DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY status_index (status),
			KEY priority_index (priority),
			KEY created_by_index (created_by)
		) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    public function deactivatePlugin()
    {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

new ModularTaskManager();
