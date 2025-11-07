<?php
namespace DFrame\Reports\Interface;

/**
 * HandlerInterface defines the methods required for error handling classes.
 */
interface HandlerInterface
{
    /**
     * Constructor to initialize the handler.
     */
    public function __construct();
    /**
     * Handles PHP errors.
     */
    public function handleError();
    /**
     * Handles uncaught exceptions.
     */
    public function handleException();
    /**
     * Handles parse errors.
     */
    public function handlerParse();
    /**
     * Handles runtime errors.
     */
    public function handlerRuntime();
    /**
     * Logs an error with given details.
     */
    public function logError($severity, $message, $file, $line);
}
