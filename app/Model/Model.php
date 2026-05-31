<?php

namespace App\Model;

use DLight\Database\Adapter\MysqliAdapter;
use DLight\Database\Adapter\Sqlite3Adapter;
use DLight\Database\Adapter\PdoMysqlAdapter;
use DLight\Database\Adapter\PdoSqliteAdapter;
use DLight\Database\DatabaseManager;
use DLight\Database\Exception\CallWrongMethodOnDbDesign;

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
 * @method mixed execute()
 * @method array fetchAll()
 * @method mixed fetch(string $type = 'assoc')
 * @method mixed first(string $type = 'assoc')
 * @method array get()
 * @method array mapper()
 * @method array builder()
 *
 * @method array|null find($id)
 * @method array all()
 * @method array where($column, $value, $operator)
 * @method mixed create(array $data)
 * @method mixed update($id, array $data)
 * @method mixed delete($id)
 * @method mixed insertGetId(array $data)
 * @method bool executeUpdate(array $data)
 * @method bool executeDelete()
 * 
 * @method mixed mapper()
 * @method mixed builder()
 */
class Model extends DatabaseManager
{
    // /**
    //  * Summary of table
    //  * @var string
    //  */
    // protected $table;
    // /**
    //  * Optional columns to select (string|array|null)
    //  * @var mixed
    //  */
    // protected $selectable;
    /**
     * Check if this model uses SoftDelete trait
     */
    protected function usesSoftDelete(): bool
    {
        $class = static::class;
        $softDeleteTrait = \DLight\Database\Traits\SoftDelete::class;
        
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
            $selectable = property_exists($this, 'selectable') ? $this->selectable : null;
            $this->mapper = $this->getMapper($this->table, $this->usesSoftDelete(), $selectable);
        }
    }

    /**
     * Set the table for the query.
     * @return static
     */
    public static function table(string $table): self
    {
        $instance = new static();
        $instance->table = $table;
        $selectable = property_exists($instance, 'selectable') ? $instance->selectable : null;
        $instance->mapper = $instance->getMapper($table, $instance->usesSoftDelete(), $selectable);
        return $instance;
    }

    /**
     * Create a mapper-based model instance regardless of env DB_DESIGN.
     */
    public static function mapper(): self
    {
        $instance = new static();
        return $instance->switchDesign('mapper');
    }

    /**
     * Switch current model instance to builder design.
     */
    public function builder(): self
    {
        return $this->switchDesign('builder');
    }

    /**
     * Handle dynamic method calls into the model.
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (!is_object($this->mapper) || !method_exists($this->mapper, $method)) {
            $design = (string) env('DB_DESIGN');
            throw CallWrongMethodOnDbDesign::fromMethod((string) $method, $design);
        }

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
        if (!is_object($instance->mapper) || !method_exists($instance->mapper, $method)) {
            $design = (string) env('DB_DESIGN');
            throw CallWrongMethodOnDbDesign::fromMethod((string) $method, $design);
        }

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
