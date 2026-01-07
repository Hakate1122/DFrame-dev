<?php

namespace DFrame\Application;

use DFrame\Database\Adapter\MysqliAdapter;
use DFrame\Database\Adapter\Sqlite3Adapter;
use DFrame\Database\Adapter\PdoMysqlAdapter;
use DFrame\Database\Adapter\PdoSqliteAdapter;
use DFrame\Database\DatabaseManager;

/**
 * #### Database handler
 * Database proxy to Mapper/Builder.
 *
 * The actual implementation behind this model depends on env('DB_DESIGN'):
 * - 'builder': forwards to Query Builder with fluent methods
 * - 'mapper': forwards to Mapper with CRUD helpers
 *
 * @method \Craft\Database\Interfaces\BuilderInterface table(string $table)
 * @method \Craft\Database\Interfaces\BuilderInterface select($columns = ['*'])
 * @method \Craft\Database\Interfaces\BuilderInterface where($column, $value = null, $operator = null)
 * @method \Craft\Database\Interfaces\BuilderInterface insert(array $data)
 * @method \Craft\Database\Interfaces\BuilderInterface update(array $data)
 * @method \Craft\Database\Interfaces\BuilderInterface delete()
 * @method \Craft\Database\Interfaces\BuilderInterface softDelete(int|string|null $id = null)
 * @method \Craft\Database\Interfaces\BuilderInterface execute()
 * @method \Craft\Database\Interfaces\BuilderInterface fetchAll()
 * @method \Craft\Database\Interfaces\BuilderInterface fetch(string $type = 'assoc')
 * @method \Craft\Database\Interfaces\BuilderInterface first(string $type = 'assoc')
 * @method \Craft\Database\Interfaces\BuilderInterface get()
 *
 * @method \Craft\Database\Interfaces\MapperInterface find($id)
 * @method \Craft\Database\Interfaces\MapperInterface findOrFail($id)
 * @method \Craft\Database\Interfaces\MapperInterface all()
 * @method \Craft\Database\Interfaces\MapperInterface where($column, $value, $operator)
 * @method \Craft\Database\Interfaces\MapperInterface create(array $data)
 * @method \Craft\Database\Interfaces\MapperInterface update($id, array $data)
 * @method \Craft\Database\Interfaces\MapperInterface delete($id)
 * @method \Craft\Database\Interfaces\MapperInterface insertGetId(array $data)
 * @method \Craft\Database\Interfaces\MapperInterface executeUpdate(array $data)
 * @method \Craft\Database\Interfaces\MapperInterface executeDelete()
 */
class DB extends DatabaseManager
{
    /**
     * Summary of table
     * @var string
     */
    protected $table;

    /**
     * Check if this class uses SoftDelete trait
     * Allows child models extending DB to automatically detect SoftDelete trait
     * @return bool
     */
    protected function usesSoftDelete(): bool
    {
        $class = static::class;
        $softDeleteTrait = \DFrame\Database\Traits\SoftDelete::class;
        
        // Check traits in this class and parent classes
        do {
            $traits = class_uses($class);
            if ($traits && in_array($softDeleteTrait, $traits)) {
                return true;
            }
        } while ($class = get_parent_class($class));
        
        return false;
    }

    /**
     * Initialize the DB instance.
     */
    public function __construct()
    {
        parent::__construct();
        if ($this->table) {
            // If child class has SoftDelete trait, use soft delete automatically
            $this->mapper = $this->getMapper($this->table, $this->usesSoftDelete());
        }
    }

    /**
     * Set the table for the query.
     * @param string $table
     * @return static
     */
    public static function table(string $table): self
    {
        $instance = new static();
        $instance->table = $table;
        // If called from child class with SoftDelete trait, auto-detect
        // If called directly from DB class, use hard delete (useSoftDelete = false)
        $instance->mapper = $instance->getMapper($table, $instance->usesSoftDelete());
        return $instance;
    }

    /**
     * Handle dynamic method calls into the model.
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->mapper, $method], $args);
    }

    /**
     * Handle dynamic static method calls into the model.
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $instance = new static();
        return call_user_func_array([$instance->mapper, $method], $args);
    }

    /**
     * Get the database adapter instance.
     * @return MysqliAdapter|PdoMysqlAdapter|PdoSqliteAdapter|Sqlite3Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}
