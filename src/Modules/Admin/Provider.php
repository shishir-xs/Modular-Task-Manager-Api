<?php

namespace TaskManager\Modules\Admin;

use TaskManager\Supports\Abstracts\AbstractLoader;

class Provider extends AbstractLoader
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'registerAdminMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
    }

    /**
     * Register admin menu page
     */
    public function registerAdminMenu()
    {
        add_menu_page(
            __('Task Manager', 'modular-task-manager'),
            __('Tasks', 'modular-task-manager'),
            'manage_options',
            'task-manager',
            [$this, 'renderAdminPage'],
            'dashicons-list-view',
            30
        );
    }

    /**
     * Render admin page
     */
    public function renderAdminPage()
    {
        require_once plugin_dir_path(__FILE__) . 'views/admin-page.php';
    }

    /**
     * Enqueue React app and styles
     */
    public function enqueueAdminAssets($hook)
    {
        // Only load on our admin page
        if ('toplevel_page_task-manager' !== $hook) {
            return;
        }

        $plugin_dir = task_manager_config()->get('plugin.base_path');
        $plugin_url = task_manager_config()->get('plugin.url');
        $asset_file = $plugin_dir . 'assets/admin/build/assets.php';

        // Check if built assets exist
        if (file_exists($asset_file)) {
            $asset_data = include $asset_file;
            
            wp_enqueue_script(
                'task-manager-admin',
                $plugin_url . 'assets/admin/build/index.js',
                $asset_data['index.js']['dependencies'] ?? ['react', 'react-dom', 'wp-polyfill'],
                $asset_data['index.js']['version'] ?? TASK_MANAGER_VERSION,
                true
            );

            wp_enqueue_style(
                'task-manager-admin',
                $plugin_url . 'assets/admin/build/index.css',
                [],
                $asset_data['index.js']['version'] ?? TASK_MANAGER_VERSION
            );
        } else {
            // Development mode - load from source
            wp_enqueue_script(
                'task-manager-admin',
                $plugin_url . 'assets/admin/src/index.js',
                ['react', 'react-dom', 'wp-polyfill'],
                TASK_MANAGER_VERSION,
                true
            );
        }

        // Localize script with data
        wp_localize_script('task-manager-admin', 'taskManagerData', [
            'apiUrl' => rest_url('task-manager/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'siteUrl' => get_site_url(),
        ]);
    }
}
