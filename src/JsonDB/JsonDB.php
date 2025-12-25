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
        // Tự động tạo file nếu chưa tồn tại
        if (!file_exists($file)) {
            // Sử dụng LOCK_EX để đảm bảo an toàn ngay từ lúc tạo
            file_put_contents($file, json_encode([]), LOCK_EX);
        }
    }

    /**
     * Đọc dữ liệu an toàn và xử lý lỗi JSON
     */
    public function all(): array {
        if (!file_exists($this->file)) {
            return [];
        }

        $content = file_get_contents($this->file);
        $data = json_decode($content, true);

        // Kiểm tra lỗi JSON (ví dụ: file bị sửa bậy bạ)
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Lỗi đọc file JSON: " . json_last_error_msg());
        }

        return is_array($data) ? $data : [];
    }

    /**
     * Thêm dữ liệu mới với ID tự tăng
     */
    public function insert(array $data): int {
        $db = $this->all();
        
        // Tạo ID tự động (Nếu mảng rỗng thì ID = 1, ngược lại lấy ID cuối + 1)
        // Lưu ý: Cách này đơn giản, với hệ thống lớn nên dùng UUID
        $lastItem = end($db);
        $newId = isset($lastItem['id']) ? $lastItem['id'] + 1 : 1;
        
        $data['id'] = $newId;
        $db[] = $data;

        $this->save($db);
        
        return $newId;
    }

    /**
     * Tìm kiếm bản ghi theo Key-Value
     */
    public function where(string $key, $value): array {
        $db = $this->all();
        // Sử dụng array_filter để lọc dữ liệu
        return array_values(array_filter($db, function($item) use ($key, $value) {
            return isset($item[$key]) && $item[$key] == $value;
        }));
    }

    /**
     * Tìm 1 bản ghi cụ thể theo ID
     */
    public function find(int $id): ?array {
        $result = $this->where('id', $id);
        return $result[0] ?? null;
    }

    /**
     * Cập nhật dữ liệu theo ID
     */
    public function update(int $id, array $newData): bool {
        $db = $this->all();
        $found = false;

        foreach ($db as $key => $item) {
            if (isset($item['id']) && $item['id'] == $id) {
                // Merge dữ liệu cũ với dữ liệu mới
                $db[$key] = array_merge($item, $newData);
                // Đảm bảo ID không bị đổi
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
     * Xóa dữ liệu theo ID
     */
    public function delete(int $id): bool {
        $db = $this->all();
        $initialCount = count($db);

        // Lọc bỏ phần tử có ID trùng khớp
        $db = array_filter($db, function($item) use ($id) {
            return isset($item['id']) && $item['id'] != $id;
        });

        if (count($db) < $initialCount) {
            // Re-index mảng để tránh tạo mảng thưa (sparse array) trong JSON
            $this->save(array_values($db));
            return true;
        }

        return false;
    }

    /**
     * Hàm lưu private để tái sử dụng logic ghi file
     */
    private function save(array $data): void {
        // JSON_PRETTY_PRINT: Dễ đọc
        // LOCK_EX: Khóa file độc quyền (Exclusive Lock) - CỰC KỲ QUAN TRỌNG
        // Ngăn chặn process khác ghi vào file khi đang xử lý
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if (file_put_contents($this->file, $json, LOCK_EX) === false) {
             throw new \Exception("Không thể ghi vào file DB.");
        }
    }
}