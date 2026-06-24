<?php

declare(strict_types=1);

namespace DLight\Database\Exception;

/**
 * Exception thrown when a method is called that does not exist in the database layer.
 * 
 * For example, if you call a method that is not defined in either the Mapper or Builder design, this exception will be thrown.
 * 
 * @see \BadMethodCallException
 */
class CallUnknownMethodException extends \BadMethodCallException
{
    public function __construct(string $method)
    {
        parent::__construct("Call to unknown method: $method");
    }
}
