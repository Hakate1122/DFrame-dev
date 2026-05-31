<?php

declare(strict_types=1);

namespace DLight\Database\Exception;

/**
 * Exception thrown when an unsupported design pattern is requested.
 * 
 * @see \BadMethodCallException
 */
class UnsupportedDesignException extends \BadMethodCallException {
    public function __construct(string $design)
    {
        parent::__construct("Unsupported design pattern: $design");
    }
}
