<?php

namespace DFrame\JsonDB;

/**
 * JsonDB is a simple JSON file-based database system.
 * It allows basic CRUD operations on a JSON file, treating it as a database table.
 * 
 * Note: This is suitable for small-scale applications or prototyping.
 * For production systems, consider using a full-fledged database.
 */
class JsonDB {
    private string $file;

    public function __construct(string $file) {
        $this->file = $file;
        if (!file_exists($file)) {
            file_put_contents($file, json_encode([]), LOCK_EX);
        }
    }

    /**
     * Retrieve all records from the JSON database
     */
    public function all(): array {
        if (!file_exists($this->file)) {
            return [];
        }

        $content = file_get_contents($this->file);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Lỗi đọc file JSON: " . json_last_error_msg());
        }

        return is_array($data) ? $data : [];
    }

    /**
     * Insert a new record with an auto-incremented ID.
     */
    public function insert(array $data): int {
        $db = $this->all();
    
        $lastItem = end($db);
        $newId = isset($lastItem['id']) ? $lastItem['id'] + 1 : 1;
        
        $data['id'] = $newId;
        $db[] = $data;

        $this->save($db);
        
        return $newId;
    }

    /**
     * Find records by key-value pair.
     */
    public function where(string $key, $value): array {
        $db = $this->all();

        return array_values(array_filter($db, function($item) use ($key, $value) {
            return isset($item[$key]) && $item[$key] == $value;
        }));
    }

    /**
     * Find a specific record by ID.
     */
    public function find(int $id): ?array {
        $result = $this->where('id', $id);
        return $result[0] ?? null;
    }

    /**
     * Update a record by ID.
     */
    public function update(int $id, array $newData): bool {
        $db = $this->all();
        $found = false;

        foreach ($db as $key => $item) {
            if (isset($item['id']) && $item['id'] == $id) {
                $db[$key] = array_merge($item, $newData);
                $db[$key]['id'] = $id; 
                $found = true;
                break;
            }
        }

        if ($found) {
            $this->save($db);
        }

        return $found;
    }

    /**
     * Delete a record by ID.
     */
    public function delete(int $id): bool {
        $db = $this->all();
        $initialCount = count($db);

        $db = array_filter($db, function($item) use ($id) {
            return isset($item['id']) && $item['id'] != $id;
        });

        if (count($db) < $initialCount) {

            $this->save(array_values($db));
            return true;
        }

        return false;
    }

    /**
     * Save the current state of the database back to the JSON file.
     */
    private function save(array $data): void {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if (file_put_contents($this->file, $json, LOCK_EX) === false) {
             throw new \Exception("Không thể ghi vào file DB.");
        }
    }
}