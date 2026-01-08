<?php

use TaskManager\Boot;
use TaskManager\Supports\Config;

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
    // ============================================================
    // Properties
    // ============================================================

    protected static $pluginDir;
    protected static $initiated;

    // ============================================================
    // Constructor & Initialization
    // ============================================================

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

    /**
     * Initialize the plugin components
     * Runs on WordPress 'plugins_loaded' hook
     */
    public function initiate()
    {
        // 1. Register autoloader
        spl_autoload_register([$this, 'autoloadClasses']);
        
        // 2. Setup configuration
        self::manageConfig();
        
        // 3. Load helper functions
        self::loadFunctions();
        
        // 4. Bootstrap the plugin
        new Boot();
    }

    // ============================================================
    // Autoloader
    // ============================================================

    /**
     * PSR-4 autoloader for TaskManager namespace
     * Automatically loads class files when they are used
     */
    public function autoloadClasses($class)
    {
        $prefix = 'TaskManager\\';
        $base_dir = self::$pluginDir . 'src/';

        // Exit if class doesn't start with the prefix
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        // Remove namespace prefix to get relative class path
        $relative_class = substr($class, $len);
        
        // Convert namespace to file path
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    }

    // ============================================================
    // Configuration & Setup
    // ============================================================

    /**
     * Setup plugin configuration
     * Stores all plugin settings in central Config instance
     */
    public static function manageConfig()
    {
        $config = Config::instance();

        $config->add('plugin.name', 'Modular Task Manager');
        $config->add('plugin.version', TASK_MANAGER_VERSION);
        $config->add('plugin.path', trailingslashit(self::$pluginDir));
        $config->add('plugin.file', __FILE__);
        $config->add('plugin.url', trailingslashit(plugin_dir_url(__FILE__)));
        $config->add('plugin.prefix', TASK_MANAGER_PREFIX);
        $config->add('plugin.src_path', __DIR__);
    }


    /**
     * Load all helper functions from src/functions directory
     */
    public static function loadFunctions()
    {
        foreach (glob(self::$pluginDir . 'src/functions/*.php') as $file) {
            include_once $file;
        }
    }

    // ============================================================
    // WordPress Hooks - Activation & Deactivation
    // ============================================================

    /**
     * Plugin activation hook
     * Creates database tables and sets up initial configuration
     */
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

    /**
     * Plugin deactivation hook
     * Cleanup tasks on plugin deactivation
     */
    public function deactivatePlugin()
    {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

new ModularTaskManager();
