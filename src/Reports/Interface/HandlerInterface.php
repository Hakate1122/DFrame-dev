<?php

namespace DFrame\Reports\Interface;

/**
 * HandlerInterface - Interface for error and exception handlers
 */
interface HandlerInterface
{
    public function __construct(bool $saveLog = false, string $logFile = 'errors.log');
    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool;
    public function handleException(\Throwable $exception): void;
    public function handleParse(): void;
    public function handleRuntime(): void;
    public function log(string $type, string $message, string $file, int $line, array $context = []): void;
}
