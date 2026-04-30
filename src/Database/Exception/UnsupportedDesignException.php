<?php

declare(strict_types=1);

namespace DFrame\Database\Exception;

/**
 * Exception thrown when an unsupported design pattern is requested.
 * 
 * @see \BadMethodCallException
 */
class UnsupportedDesignException extends \BadMethodCallException {}
