<?php
namespace DFrame\Database\Interfaces;

/**
 * Interface for data mappers providing CRUD operations.
 */
interface MapperInterface {
    /** Find a record by its ID. */
    public function find($id);
    /** Find a record by its ID or throw an exception if not found. */
    public function findOrFail($id);
    /** Get all records. */
    public function all();
    /** Add a where condition. */
    public function where($column, $value, $operator);
    /** Create a new record. */
    public function create(array $data);
    /** Update a record by its ID. */
    public function update($id, array $data);
    /** Delete a record by its ID. */
    public function delete($id);
    /** Insert a new record and get its ID. */
    public function insertGetId(array $data);
    /** Execute an update operation. */
    public function executeUpdate(array $data);
    /** Execute a delete operation. */
    public function executeDelete(array $data);
}