<?php

namespace TaskManager;

class Boot
{
    public function __construct()
    {
        task_manager_config()->add('plugin.src_path', __DIR__);
        
        new Modules\Task\Provider();
    }
}
