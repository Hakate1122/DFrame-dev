<?php
namespace DFrame\Database\Interfaces;

/**
 * Interface for database adapters (e.g., PDO, MySQLi).
 */
interface AdapterInterface{
    /** Connect to the database */
    public function connect(array $config);
    /** Disconnect from the database */
    public function disconnect();
    /** Execute a query with optional parameters */
    public function query($sql, $params = []);
    /** Fetch a single result */
    public function fetch($result, $type = 'assoc');
    /** Fetch all results */
    public function fetchAll($result, $type = 'assoc');
    /** Get the last inserted ID */
    public function lastInsertId();
    /** Get the number of affected rows */
    public function beginTransaction();
    /** Commit the current transaction */
    public function commit();
    /** Rollback the current transaction */
    public function rollback();
    /** Get the last error message */
    public function getError();
}