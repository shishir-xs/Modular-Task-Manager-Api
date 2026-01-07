<?php

namespace TaskManager\Modules\Task;

use TaskManager\Supports\Abstracts\AbstractLoader;

class Provider extends AbstractLoader
{
    public function __construct()
    {
        // Load service classes (no need to instantiate)
        $this->classLoader([
            plugin_dir_path(__FILE__) . 'Services',
        ]);

        // Load and instantiate REST endpoint classes
        $this->loadRESTClasses(
            plugin_dir_path(__FILE__) . 'REST',
            'TaskManager\\Modules\\Task\\REST'
        );
    }
}
