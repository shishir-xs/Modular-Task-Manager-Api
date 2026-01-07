<?php

namespace TaskManager\Modules\Task\Data;

defined('ABSPATH') || exit;

use TaskManager\Supports\Abstracts\AbstractModel;

class TaskModel extends AbstractModel
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected function getTable(): string
    {
        return TASK_MANAGER_DB_PREFIX . 'task_manager_tasks';
    }

    public static function getByStatus(string $status): array
    {
        global $wpdb;
        $instance = new static();
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$instance->table} WHERE status = %s ORDER BY id DESC", $status),
            ARRAY_A
        );
        
        $models = [];
        foreach ($results as $row) {
            $model = new static();
            $model->id = $row['id'];
            $model->attributes = $row;
            $models[] = $model;
        }
        
        return $models;
    }

    public static function getByPriority(string $priority): array
    {
        global $wpdb;
        $instance = new static();
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$instance->table} WHERE priority = %s ORDER BY id DESC", $priority),
            ARRAY_A
        );
        
        $models = [];
        foreach ($results as $row) {
            $model = new static();
            $model->id = $row['id'];
            $model->attributes = $row;
            $models[] = $model;
        }
        
        return $models;
    }

    public function markAsCompleted(): bool
    {
        $this->status = 'completed';
        $this->completed_at = date('Y-m-d H:i:s');
        return $this->save();
    }
}
