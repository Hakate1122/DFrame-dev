<?php
/**
 * WebSocket Chat Server
 * 
 * Khởi chạy server WebSocket cho ứng dụng Chat
 * 
 * Usage:
 *   php app/Chat/server.php
 * 
 * Hoặc với host và port tùy chỉnh:
 *   php app/Chat/server.php --host=127.0.0.1 --port=8080
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Chat\Chat;

// Parse command line arguments
$options = getopt('', ['host:', 'port:']);
$host = $options['host'] ?? '0.0.0.0';
$port = isset($options['port']) ? (int)$options['port'] : 9501;

// Create and start the WebSocket server
$chat = new Chat($host, $port);
$chat->start();

