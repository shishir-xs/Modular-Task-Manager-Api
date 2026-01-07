<?php

namespace TaskManager\Supports\Abstracts;

abstract class AbstractModel
{
    protected $wpdb;
    protected $table;
    protected $fillable = [];
    protected $attributes = [];
    protected $id;

    public function __construct(array $attributes = [])
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $this->getTable();
        $this->fill($attributes);
    }

    abstract protected function getTable(): string;
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    public function save(): bool
    {
        if ($this->id) {
            // Update existing record
            $result = $this->wpdb->update(
                $this->table,
                $this->attributes,
                ['id' => $this->id]
            );
        } else {
            // Insert new record
            $result = $this->wpdb->insert($this->table, $this->attributes);
            if ($result) {
                $this->id = $this->wpdb->insert_id;
            }
        }
        return $result !== false;
    }

    public static function find(int $id): ?static
    {
        global $wpdb;
        $instance = new static();
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$instance->table} WHERE id = %d", $id),
            ARRAY_A
        );

        if (!$row) {
            return null;
        }

        $instance->id = $row['id'];
        $instance->attributes = $row;
        return $instance;
    }

    public static function all(): array
    {
        global $wpdb;
        $instance = new static();
        $results = $wpdb->get_results("SELECT * FROM {$instance->table} ORDER BY id DESC", ARRAY_A);
        
        $models = [];
        foreach ($results as $row) {
            $model = new static();
            $model->id = $row['id'];
            $model->attributes = $row;
            $models[] = $model;
        }
        
        return $models;
    }

    public function delete(): bool
    {
        if (!$this->id) {
            return false;
        }
        return $this->wpdb->delete($this->table, ['id' => $this->id]) !== false;
    }

    public function getID(): ?int
    {
        return $this->id;
    }

    public function __get(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, $value): void
    {
        if (in_array($key, $this->fillable)) {
            $this->attributes[$key] = $value;
        }
    }

    public function toArray(): array
    {
        return array_merge(['id' => $this->id], $this->attributes);
    }
}
