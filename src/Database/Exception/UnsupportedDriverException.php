<?php

namespace DFrame\Database\Exception;

/**
 * Exception thrown when an unsupported database driver is requested.
 * 
 * @see \BadMethodCallException
 */
class UnsupportedDriverException extends \BadMethodCallException {}