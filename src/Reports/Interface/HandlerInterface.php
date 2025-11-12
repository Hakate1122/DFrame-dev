<?php
namespace DFrame\Reports\Interface;

abstract class HandlerInterface
{
    abstract public function __construct(bool $saveLog = false, string $logFile = 'errors.log');
    abstract public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool;
    abstract public function handleException(\Throwable $exception): void;
    abstract public function handleParse(): void;
    abstract public function handleRuntime(): void;
    abstract public function log(string $type, string $message, string $file, int $line, array $context = []): void;
}