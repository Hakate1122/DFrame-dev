<?php
namespace App\Model;

use Core\Database\DatabaseManager;

/**
 * Dynamic model proxy to Mapper/Builder.
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
 */
class Model extends DatabaseManager
{
    /**
     * Summary of table
     * @var string
     */
    protected $table;

    public function __construct()
    {
        parent::__construct();
        $this->mapper = $this->getMapper($this->table);
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
}