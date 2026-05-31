<?php

declare(strict_types=1);

namespace DLight\Database\Exception;

/**
 * Exception thrown when an unsupported database driver is requested.
 * 
 * @see \BadMethodCallException
 */
class UnsupportedDriverException extends \BadMethodCallException {
    public function __construct(string $driver)
    {
        parent::__construct("Unsupported database driver: $driver");
    }
}
