<?php

namespace TaskManager\Modules\Task;

use TaskManager\Supports\Abstracts\AbstractLoader;

class Provider extends AbstractLoader
{
    public function __construct()
    {
        // Load and instantiate REST endpoint classes
        $this->loadRESTClasses(
            plugin_dir_path(__FILE__) . 'REST',
            'TaskManager\\Modules\\Task\\REST'
        );
    }
}
