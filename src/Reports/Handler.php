<?php

namespace Core\Reports;

use Core\Reports\Interface\HandlerInterface;

/**
 * #### ErrorReporting class for handling PHP errors and rendering error pages.
 *
 * This class provides functionality to log errors, render error pages, and handle PHP errors gracefully.
 */
class Handler extends \Exception implements HandlerInterface
{
    private $saveLog;
    private $logFile;

    /**
     * Constructor for error handling setup or throwing exceptions.
     *
     * When used for error handling setup, it registers the error handler.
     * When used for throwing exceptions, it constructs an exception with the provided details.
     *
     * @param mixed $messageOrSaveLog Error message or saveLog flag (default: false).
     * @param mixed $codeOrLogFile Error code or log file name (default: 'error.log').
     * @param int $severity Error severity (default: 0).
     * @param string|null $filename File where the error occurred (default: null).
     * @param int|null $line Line number where the error occurred (default: null).
     * @param \Throwable|null $previous Previous exception (default: null).
     */
    public function __construct($messageOrSaveLog = false, $codeOrLogFile = 'error.log', $severity = 0, $filename = null, $line = null, ?\Throwable $previous = null)
    {
        // Check if the constructor is used for error handling setup
        if (is_bool($messageOrSaveLog) && is_string($codeOrLogFile)) {
            $this->saveLog = $messageOrSaveLog;
            $this->logFile = $codeOrLogFile;
            set_error_handler([$this, 'handleError']);
        } else {
            // Used for throwing an exception
            parent::__construct($messageOrSaveLog, is_int($codeOrLogFile) ? $codeOrLogFile : 0, $previous);
            if ($this->saveLog) {
                $this->logError($severity ?: $this->getCode(), $messageOrSaveLog, $filename ?: $this->getFile(), $line ?: $this->getLine());
            }
            self::render($messageOrSaveLog, $filename ?: $this->getFile(), $line ?: $this->getLine(), $severity ?: $this->getCode());
        }
    }
    public static function sign()
    {
    }
    public function handleError()
    {
    }
    public function handleException()
    {
    }
    public function handlerParse()
    {
    }
    public function handlerRuntime()
    {
    }
    public function logError($severity, $message, $file, $line)
    {
    }
    public function render(string $type, string $message, string $file, int $line): void
    {
    }
}
