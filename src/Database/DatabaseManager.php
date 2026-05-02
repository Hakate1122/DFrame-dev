<?php

namespace DFrame\Database;

use DFrame\Database\Adapter\MysqliAdapter;
use DFrame\Database\Adapter\PdoMysqlAdapter;
use DFrame\Database\Adapter\Sqlite3Adapter;
use DFrame\Database\Adapter\PdoSqliteAdapter;

use DFrame\Database\Exception\UnsupportedDesignException;
use DFrame\Database\Exception\UnsupportedDriverException;

use DFrame\Database\Mapper\MysqlMapper;
use DFrame\Database\Mapper\SqliteMapper;

use DFrame\Database\QueryBuilder\MysqlBuilder;
use DFrame\Database\QueryBuilder\SqliteBuilder;

use function \in_array;

/**
 * **Database Manager**
 * 
 * Manages database connections and mappers/builders based on configuration.
 * 
 * It supports different database drivers and design patterns:
 * - Drivers: `mysqli`, `pdo_mysql`, `sqlite3`, `pdo_sqlite`, and more if needed
 * - Designs: `mapper` (active record style) and `builder` (query builder style)
 */
class DatabaseManager
{
    // constants for supported drivers and designs.
    /** Supported database drivers */
    protected const SUPPORTED_DRIVERS = ['mysqli', 'pdo_mysql', 'sqlite3', 'pdo_sqlite'];
    /** Supported database designs */
    protected const SUPPORTED_DESIGNS = ['mapper', 'builder'];

    // properties for adapter, mapper/builder class.
    /** Adapter instance */
    protected $adapter;
    /** Mapper or Builder instance */
    protected $mapper;
    /** Mapper or Builder class name based on design */
    protected $mapperClass;

    // properties for database (optional).
    /**
     * Table name for the mapper/builder instance
     * @var string
     */
    protected $table;

    /**
     * Selectable columns for table queries (string|array|null)
     * @var string|array|null
     */
    protected $selectable;
    /**
     * Fillable fields for mass assignment
     * @var string|array|null
     */
    protected $fillable;

    public function __construct()
    {
        $driver = env('DB_DRIVER');
        if (!$driver) {
            throw new \InvalidArgumentException("DB_DRIVER is not set.");
        }
        if (!in_array($driver, self::SUPPORTED_DRIVERS, true)) {
            throw new UnsupportedDriverException("Invalid DB_DRIVER: $driver" . ". Accepts: " . implode(', ', self::SUPPORTED_DRIVERS) . ".");
        }

        $design = env('DB_DESIGN');
        if (!$design) {
            throw new \InvalidArgumentException("DB_DESIGN is not set.");
        }
        if (!in_array($design, self::SUPPORTED_DESIGNS, true)) {
            throw new UnsupportedDesignException("Invalid DB_DESIGN: $design" . ". Accepts: " . implode(', ', self::SUPPORTED_DESIGNS) . ".");
        }

        $config = $this->getConfig($driver);

        switch ($driver) {
            case 'mysqli':
                $this->adapter = new MysqliAdapter();
                break;
            case 'sqlite3':
                $this->adapter = new Sqlite3Adapter();
                break;
            case 'pdo_sqlite':
                $this->adapter = new PdoSqliteAdapter();
                break;
            case 'pdo_mysql':
            default:
                $this->adapter = new PdoMysqlAdapter();
        }
        $this->adapter->connect($config);

        $this->mapperClass = $this->resolveMapperClass($design, $driver);
    }

    /**
     * Resolve mapper/builder class from design and driver.
     */
    protected function resolveMapperClass(string $design, string $driver): string
    {
        if (!in_array($design, self::SUPPORTED_DESIGNS, true)) {
            throw new UnsupportedDesignException("Invalid DB_DESIGN: $design" . ". Accepts: " . implode(', ', self::SUPPORTED_DESIGNS) . ".");
        }

        if ($design === 'mapper') {
            if (in_array($driver, ['mysqli', 'pdo_mysql'], true)) {
                return MysqlMapper::class;
            }
            return SqliteMapper::class;
        }

        if (in_array($driver, ['mysqli', 'pdo_mysql'], true)) {
            return MysqlBuilder::class;
        }

        return SqliteBuilder::class;
    }

    /**
     * Switch current instance database design dynamically.
     */
    public function switchDesign(string $design): self
    {
        $driver = (string) env('DB_DRIVER');
        $this->mapperClass = $this->resolveMapperClass($design, $driver);

        if (!empty($this->table)) {
            $selectable = property_exists($this, 'selectable') ? $this->selectable : null;
            $useSoftDelete = method_exists($this, 'usesSoftDelete') ? (bool) $this->usesSoftDelete() : false;
            $this->mapper = $this->getMapper($this->table, $useSoftDelete, $selectable);
        }

        return $this;
    }

    /**
     * Get database configuration based on driver
     * @param mixed $driver
     * 
     */
    protected function getConfig($driver)
    {
        if (str_contains($driver, 'sqlite')) {
            return [
                'database' => env('DB_NAME') . '.db',
            ];
        }
        if (str_contains($driver, 'mysql')) {
            return [
                'host'     => env('DB_HOST'),
                'port'     => env('DB_PORT'),
                'user'     => env('DB_USER'),
                'password' => env('DB_PASS') ?? null,
                'database' => env('DB_NAME'),
            ];
        }
        return null;
    }

    /**
     * Get the current database adapter
     * @return MysqliAdapter|PdoMysqlAdapter|PdoSqliteAdapter|Sqlite3Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Get a mapper/builder instance for a specific table
     *
     * @param mixed $table The table name for the mapper/builder
     * @param bool $useSoftDelete Whether to use soft delete (default: false)
     * @param mixed $selectable Optional columns to select (string|array|null)
     * @return object
     */
    public function getMapper($table, $useSoftDelete = false, $selectable = null)
    {
        $mapper = new $this->mapperClass($this->adapter, $table, $useSoftDelete);

        if ($selectable !== null && is_object($mapper) && method_exists($mapper, 'select')) {
            $mapper->select($selectable);
        }

        return $mapper;
    }
}
