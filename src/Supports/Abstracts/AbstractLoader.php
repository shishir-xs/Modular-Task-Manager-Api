<?php

namespace TaskManager\Supports\Abstracts;

abstract class AbstractLoader
{
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
}
