<?php
namespace DLight\Database\Adapter;

use DLight\Database\Interfaces\AdapterInterface;

/**
 * #### PDO MySQL Database Adapter using pdo_mysql extension
 * **Require**: the `pdo` and `pdo_mysql` PHP extensions.
 */
class PdoMysqlAdapter implements AdapterInterface
{
    protected $pdo;

    /**
     * Get connection - Connect to a MySQL database using PDO.
     * 
     * @param array $config Configuration array with keys:
     * - `host`: (string) Database host (default: `localhost`)
     * - `user`: (string) Database username (default: `root`)
     * - `password`: (string) Database password (default: empty)
     * - `database`: (string) Database name (default: empty)
     * - `port`: (int) Database port (default: 3306)
     */
    public function connect(array $config)
    {
        $dsn = 'mysql:host=' . ($config['host'] ?? 'localhost') . ';dbname=' . ($config['database'] ?? '') . ';port=' . ($config['port'] ?? 3306);
        $this->pdo = new \PDO($dsn, $config['user'] ?? 'root', $config['password'] ?? '');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function disconnect()
    {
        $this->pdo = null;
    }

    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch($result, $type = 'assoc')
    {
        return match ($type) {
            'num' => $result->fetch(\PDO::FETCH_NUM),
            'both' => $result->fetch(\PDO::FETCH_BOTH),
            'object' => $result->fetch(\PDO::FETCH_OBJ),
            default => $result->fetch(\PDO::FETCH_ASSOC),
        };
    }

    public function fetchAll($result, $type = 'assoc')
    {
        $data = [];
        while ($row = $this->fetch($result, $type)) {
            $data[] = $row;
        }
        return $data;
    }

    public function getError()
    {
        return $this->pdo?->errorInfo();
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    public function commit()
    {
        $this->pdo->commit();
    }

    public function rollback()
    {
        $this->pdo->rollBack();
    }
}