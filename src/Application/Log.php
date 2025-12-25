<?php

namespace DFrame\Application;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use \DateTime;

/**
 * A simple logger implementation adhering to PSR-3 standards.
 * 
 * This logger writes log messages to a specified file with different log levels.
 */
class Log implements LoggerInterface
{
    private $logFilePath;

    public function __construct(?string $logFilePath = null)
    {
        $this->logFilePath = $logFilePath ?? env('LOG_FILE', INDEX_DIR . 'logs/app.log');
    }

    /**
     * Log with a given level.
     * @param string $level Log level (e.g., emergency, alert, critical, error, warning, notice, info, debug)
     * @param string $message Log message
     * @param array $context Additional context data
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        if (env('LOG_LEVEL') === null) {
            return;
        }
        $level = env('LOG_LEVEL', $level);
        $contextString = !empty($context) ? ' ' . json_encode($context) : '';

        $now = (new DateTime())->format('Y-m-d H:i:s');
        $logEntry = sprintf(
            '[%s] [%s]: %s%s' . PHP_EOL,
            $now,
            strtoupper($level),
            $message,
            $contextString
        );

        @file_put_contents($this->logFilePath, $logEntry, FILE_APPEND);
    }

    // --- Define PSR-3 methods ---

    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    // ---Fast log methods ---
    /**
     * Fast log method for quick logging without instantiating the class.
     * 
     * @param string $logFilePath Path to the log file
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Additional context data
     * @return void
     */
    public static function fast(string $logFilePath, ?string $level = null, string $message, array $context = []): void
    {
        $logger = new self($logFilePath);
        if ($level === null) {
            $level = env('LOG_LEVEL', LogLevel::INFO);
        }
        $logger->log($level, $message, $context);
    }
}