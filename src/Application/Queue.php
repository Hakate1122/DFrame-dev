<?php

namespace DLight\Application;

abstract class Queue
{
    public function __construct()
    {
    }

    /**
     * Optional: install/prepare queue storage (table, index, etc.).
     */
    abstract public function install(): void;

    abstract public function push(string $job, array $data = [], ?int $availableAt = null): void;

    /**
     * Reserve and return one queued job.
     * Returns [] if empty.
     */
    abstract public function pop(string $job): array;

    /**
     * Process exactly one queued job and return the processed item.
     * Returns null if no job is available.
     */
    abstract public function process(string $job, callable|string|null $handler = null): ?array;

    abstract public function getQueue(string $job): array;

    abstract public function getQueueSize(string $job): int;

    abstract public function getQueueStatus(string $job): string;

    abstract public function getQueueProgress(string $job): float;

    abstract public function setProgress(int $id, float $progress): void;
}