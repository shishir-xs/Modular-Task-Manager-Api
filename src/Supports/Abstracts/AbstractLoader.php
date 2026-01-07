<?php

namespace TaskManager\Supports\Abstracts;

abstract class AbstractLoader
{
    /**
     * Load all PHP files from given directories
     *
     * @param array $directories
     * @return void
     */
    protected function classLoader(array $directories): void
    {
        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $files = glob($directory . '/*.php');
            foreach ($files as $file) {
                if (file_exists($file)) {
                    require_once $file;
                }
            }
        }
    }

    /**
     * Load and instantiate REST classes
     *
     * @param string $directory
     * @param string $namespace
     * @return void
     */
    protected function loadRESTClasses(string $directory, string $namespace): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = glob($directory . '/*.php');
        foreach ($files as $file) {
            if (file_exists($file)) {
                require_once $file;
                
                // Get class name from filename
                $className = basename($file, '.php');
                $fullClassName = $namespace . '\\' . $className;
                
                // Instantiate the class if it exists
                if (class_exists($fullClassName)) {
                    new $fullClassName();
                }
            }
        }
    }
}
