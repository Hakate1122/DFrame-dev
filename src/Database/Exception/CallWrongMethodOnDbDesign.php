<?php

namespace DLight\Database\Exception;

use Exception;
use function sprintf;
use function strtolower;
use function in_array;

/**
 * Exception thrown when a method is called on a database design that does not support it.
 * 
 * For example, if you call a method that is only supported by the Mapper design on a Builder design, this exception will be thrown.
 * 
 * @see \BadMethodCallException
 */

class CallWrongMethodOnDbDesign extends \BadMethodCallException
{
    private const MAPPER_METHODS = [
        'find',
        'findOrFail',
        'all',
        'create',
        'insertGetId',
        'executeUpdate',
        'executeDelete',
    ];

    private const BUILDER_METHODS = [
        'table',
        'select',
        'orWhere',
        'insert',
        'execute',
        'toSql',
        'getBindings',
        'fetchAll',
        'fetch',
        'first',
        'get',
        'softDelete',
    ];

    /**
     * Constructor for the exception.
     *
     * @param string $method The method that was called.
     * @param string $calledOn The database design that the method was called on.
     * @param string $expectedLayer The expected database design layer that supports the method.
     */
    public function __construct(
        string $method,
        string $calledOn,
        string $expectedLayer
    ) {
        parent::__construct(
            sprintf(
                'Method "%s" cannot be called on %s. It belongs to %s layer.',
                $method,
                $calledOn,
                $expectedLayer
            )
        );
    }

    /**
     * Build this exception from a method and current design.
     */
    public static function fromMethod(string $method, string $calledOn): self
    {
        return new self(
            $method,
            $calledOn,
            self::detectExpectedLayer($method)
        );
    }

    private static function detectExpectedLayer(string $method): string
    {
        if (in_array($method, ['where', 'update', 'delete'], true)) {
            return 'mapper or builder';
        }

        $normalized = strtolower($method);
        if (in_array($normalized, array_map('strtolower', self::MAPPER_METHODS), true)) {
            return 'mapper';
        }

        if (in_array($normalized, array_map('strtolower', self::BUILDER_METHODS), true)) {
            return 'builder';
        }

        return Exception::class;
    }
}