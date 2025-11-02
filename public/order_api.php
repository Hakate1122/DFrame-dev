<?php
header('Content-Type: application/json; charset=utf-8');

// Giả lập lưu file JSON (ở sản phẩm thật sẽ ghi DB)
$order = [
    'time' => date('Y-m-d H:i:s'),
    'table' => $_POST['table'] ?? 'N/A',
    'payment' => $_POST['payment'] ?? 'unknown',
    'cart' => $_POST['cart'] ?? [],
    'total' => (int)($_POST['total'] ?? 0),
];

$file = __DIR__ . '/data/orders.json';
@mkdir(dirname($file), 0777, true);
$orders = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
$orders[] = $order;
file_put_contents($file, json_encode($orders, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

echo json_encode(['message' => 'Đơn hàng đã được gửi thành công! Chờ phục vụ...']);
