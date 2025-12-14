<?php
// Simple router to log requests in a nicer format for PHP built-in server
if (php_sapi_name() !== 'cli-server') {
    return false;
}

// Serve static files directly when they exist
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$full = $_SERVER['DOCUMENT_ROOT'] . $path;
if ($path !== '/' && is_file($full)) {
    return false;
}

function now()
{
    return date('Y-m-d H:i:s');
}

function client()
{
    $addr = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $port = $_SERVER['REMOTE_PORT'] ?? '';
    return $addr . ($port ? ':' . $port : '');
}

// Buffer output from application to capture response code
ob_start();
require __DIR__ . '/index.php';
$content = ob_get_clean();
$status = http_response_code() ?: 200;

$line = sprintf('[%s] %s [%d]: %s %s', now(), client(), $status, $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
// If 404, append a short note
if ($status === 404) {
    $line .= ' (Not Found)';
}

// Print to server console
echo $line . PHP_EOL;
// Also append to a rotating log file (simple append)
@error_log($line . PHP_EOL, 3, __DIR__ . '/access.log');

// Output application response
echo $content;