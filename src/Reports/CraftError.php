<?php

namespace DFrame\Reports;

/**
 * #### ErrorReporting class for handling PHP errors and rendering error pages.
 *
 * This class provides functionality to log errors, render error pages, and handle PHP errors gracefully.
 */
class CraftError extends \ErrorException
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
            parent::__construct($messageOrSaveLog, is_int($codeOrLogFile) ? $codeOrLogFile : 0, $severity, $filename, $line, $previous);
            if ($this->saveLog) {
                $this->logError($severity ?: $this->getCode(), $messageOrSaveLog, $filename ?: $this->getFile(), $line ?: $this->getLine());
            }
            self::render($messageOrSaveLog, $filename ?: $this->getFile(), $line ?: $this->getLine(), $severity ?: $this->getCode());
        }
    }
    /**
     * Sign the exception handler.
     * @param mixed $saveLog Save log or not.
     * @param mixed $logFile Set the log file name.
     */
    public static function sign($saveLog = false, $logFile = 'exception.log')
    {
        return new self($saveLog, $logFile);
    }

    /**
     * Handle PHP errors.
     * @param int $errno The error number.
     * @param string $errstr The error message.
     * @param string $errfile The file where the error occurred.
     * @param int $errline The line number where the error occurred.
     */
    public function handleError($errno, $errstr, $errfile, $errline)
    {
        if ($this->saveLog) {
            $this->logError($errno, $errstr, $errfile, $errline);
        }
        self::render($errstr, $errfile, $errline, $errno);
        if (error_reporting() & $errno) {
            exit(1);
        }
    }

    /**
     * Log the error to a file.
     * @param int $errno The error number.
     * @param string $errstr The error message.
     * @param string $errfile The file where the error occurred.
     * @param int $errline The line number where the error occurred.
     */
    private function logError($errno, $errstr, $errfile, $errline)
    {
        $logMessage = date('Y-m-d H:i:s') . " | Error [$errno]: $errstr | File: $errfile | Line: $errline\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }

    /**
     * Render the error message.
     * @param string $message The error message.
     * @param string $file The file where the error occurred.
     * @param int $line The line number where the error occurred.
     * @param int $errno The error number.
     * @return never
     */
    public static function render($message, $file = null, $line = null, $errno = null)
    {
        http_response_code(500);
        // Clear all output buffers to display only the error page
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if ($file === null || $line === null) {
            $backtrace = debug_backtrace();
            $caller = $backtrace[0];
            $file = $caller['file'] ?? 'Unknown file';
            $line = $caller['line'] ?? 'Unknown line';
        }

        echo "<!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Error: $message</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background-color: #f8f9fa;
                    color: #333;
                    line-height: 1.5;
                    min-width: 320px;
                }

                .error-container {
                    margin: 20px;
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    overflow: hidden;
                }

                /* Responsive adjustments for screens 320px and up */
                @media (max-width: 600px) {
                    .error-container {
                        margin: 0;
                        border-radius: 0;
                        width: 100vw;
                        min-width: 320px;
                        max-width: 100vw;
                        box-shadow: none;
                    }
                    .error-header {
                        padding: 10px 12px;
                        font-size: clamp(12px, 3vw, 13px);
                    }
                    .error-tabs {
                        gap: 6px;
                    }
                    .error-tab {
                        padding: 4px 8px;
                        font-size: clamp(10px, 2.5vw, 11px);
                        min-width: 60px;
                        text-align: center;
                    }
                    .error-title {
                        padding: 12px;
                    }
                    .error-title h2 {
                        font-size: clamp(14px, 4vw, 16px);
                        margin-bottom: 6px;
                    }
                    .error-message {
                        font-size: clamp(12px, 3.5vw, 13px);
                    }
                    .code-header {
                        padding: 8px 12px;
                        font-size: clamp(10px, 2.5vw, 11px);
                    }
                    .code-viewer {
                        font-size: clamp(10px, 3vw, 12px);
                    }
                    .line-number {
                        min-width: 48px;
                        padding: 0 8px;
                        font-size: clamp(10px, 2.5vw, 11px);
                    }
                    .line-content {
                        padding: 0 12px;
                    }
                    .expand-btn {
                        font-size: clamp(10px, 2.5vw, 11px);
                        padding: 6px;
                    }
                    .related-files-container {
                        padding: 0 12px 12px;
                    }
                    .related-files-container ul {
                        font-size: clamp(10px, 3vw, 12px);
                        padding: 8px;
                    }
                }

                /* Desktop layout (unchanged) */
                .error-header {
                    background: linear-gradient(135deg, #6366f1, #8b5cf6);
                    color: white;
                    padding: 15px 20px;
                    font-weight: 500;
                    font-size: 14px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .error-tabs {
                    display: flex;
                    gap: 10px;
                }

                .error-tab {
                    background: rgba(255,255,255,0.2);
                    padding: 4px 12px;
                    border-radius: 4px;
                    font-size: 12px;
                    cursor: pointer;
                    transition: background 0.2s;
                }

                .error-tab.active {
                    background: rgba(255,255,255,0.3);
                }

                .error-content {
                    padding: 0;
                }

                .error-title {
                    padding: 20px;
                    border-bottom: 1px solid #e5e7eb;
                }

                .error-title h2 {
                    color: rgb(246, 76, 76);
                    font-size: 18px;
                    font-weight: 600;
                    margin-bottom: 8px;
                }

                .error-message {
                    color: #6b7280;
                    font-size: 14px;
                }

                .code-container {
                    background: #fafafa;
                    border-top: 1px solid #e5e7eb;
                }

                .code-header {
                    background: #f3f4f6;
                    padding: 12px 20px;
                    border-bottom: 1px solid #e5e7eb;
                    font-size: 12px;
                    color: #6b7280;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .file-path {
                    font-family: 'Monaco', 'Consolas', monospace;
                }

                .line-info {
                    color: #9ca3af;
                }

                .code-viewer {
                    font-family: 'Monaco', 'Consolas', 'Courier New', monospace;
                    font-size: 13px;
                    line-height: 1.6;
                    background: white;
                    overflow-x: auto;
                }

                .code-line {
                    display: flex;
                    min-height: 24px;
                    align-items: center;
                }

                .line-number {
                    background: #f8f9fa;
                    color: #9ca3af;
                    padding: 0 12px;
                    text-align: right;
                    min-width: 60px;
                    user-select: none;
                    border-right: 1px solid #e5e7eb;
                    font-size: 12px;
                }

                .line-content {
                    padding: 0 16px;
                    flex: 1;
                    white-space: pre;
                }

                .error-line {
                    background: rgb(61, 177, 230) !important;
                    color: #fff !important;
                    font-weight: bold;
                    border-left: 6px solid rgb(63, 174, 226);
                    box-shadow: 0 0 8px #dc262633;
                }
                .error-line .line-number {
                    background: rgb(61, 177, 230) !important;
                    color: #fff !important;
                    border-right: 1px solid #fff;
                }
                .error-line .line-content {
                    background: rgb(184, 189, 230) !important;
                    color: #fff !important;
                }

                .php-keyword {
                    color: #0ea5e9;
                    font-weight: 500;
                }

                .php-variable {
                    color: rgb(153, 143, 64);
                }

                .php-string {
                    color: #059669;
                }

                .php-comment {
                    color: #6b7280;
                    font-style: italic;
                }

                .php-function {
                    color: #7c3aed;
                }

                .expand-btn {
                    background: none;
                    border: none;
                    color: #6b7280;
                    cursor: pointer;
                    font-size: 12px;
                    padding: 4px;
                    transition: color 0.2s;
                }
                .expand-btn:hover {
                    color: #333;
                }
            </style>
        </head>
        <body>";

        echo "<div class='error-container'>";
        echo "<div class='error-header'>";
        echo "<span>Oops, a error detected!</span>";
        echo "<div class='error-tabs'>";
        echo "<span class='error-tab active' onclick='showTab(\"full\")'>Full</span>";
        echo "<span class='error-tab' onclick='showTab(\"raw\")'>Raw</span>";
        echo "</div>";
        echo "</div>";

        // Full tab content
        echo "<div class='error-content' id='full-tab'>";
        echo "<div class='error-title'>";
        echo "<h2>Error</h2>";
        echo "<div class='error-message'>" . htmlspecialchars($message, ENT_NOQUOTES) . "</div>";
        echo "<div>" . 'version:' . \DFrame\Application\App::version . "</div>";
        echo "</div>";

        if ($file && $line && file_exists($file)) {
            $filename = basename($file);
            echo "<div class='code-container'>";
            echo "<div class='code-header'>";
            echo "<span class='file-path'>/" . $filename . " in " . htmlspecialchars($file, ENT_NOQUOTES) . "</span>";
            echo "<span class='line-info'>at line " . $line . "</span>";
            echo "</div>";

            echo "<div class='code-viewer'>";

            $lines = file($file);
            $start = max($line - 5, 0);
            $end = min($line + 5, count($lines));

            for ($i = $start; $i < $end; $i++) {
                $lineNum = $i + 1;
                $lineContent = rtrim($lines[$i]);
                $isErrorLine = $lineNum === $line;

                // Simple PHP syntax highlighting
                $highlightedContent = self::highlightPhpSyntax($lineContent);

                $lineClass = $isErrorLine ? 'code-line error-line' : 'code-line';

                echo "<div class='$lineClass'>";
                echo "<div class='line-number'>" . $lineNum . "</div>";
                echo "<div class='line-content'>" . $highlightedContent . "</div>";
                echo "</div>";
            }

            echo "</div>";
            echo "</div>";
        }
        echo "</div>";

        // Raw tab content
        echo "<div class='error-content' id='raw-tab' style='display: none;'>";
        echo "<div style='padding: 20px; font-family: monospace; background: #f8f9fa; white-space: pre-wrap;'>";
        echo "Message: " . htmlspecialchars($message, ENT_NOQUOTES) . "\n";
        echo "File: " . htmlspecialchars($file ?? 'N/A', ENT_NOQUOTES) . "\n";
        echo "Line: " . ($line ?? 'N/A') . "\n";
        echo "Timestamp: " . date('c') . "\n";
        echo "Memory Usage: " . number_format(memory_get_usage(true)) . " bytes\n";
        echo "Peak Memory: " . number_format(memory_get_peak_usage(true)) . " bytes\n";
        echo "</div>";
        echo "</div>";

        // Display related files (included files)
        $includedFiles = get_included_files();
        echo "<div class='related-files-container' style='padding: 0 20px 20px 20px;'>";
        echo "<button class='expand-btn' onclick='toggleRelatedFiles()' id='related-files-btn'>Show Related Files (" . count($includedFiles) . ") ⌄</button>";
        echo "<div id='related-files-list' style='display:none; margin-top:10px;'>";
        echo "<ul style='font-family:monospace; font-size:13px; background:#f8f9fa; border-radius:6px; padding:10px;'>";
        foreach ($includedFiles as $f) {
            $isCurrent = ($f === $file);
            echo "<li" . ($isCurrent ? " style='color:#dc2626;font-weight:bold;'" : "") . ">" . htmlspecialchars($f, ENT_NOQUOTES) . "</li>";
        }
        echo "</ul>";
        echo "</div>";
        echo "</div>";

        // JavaScript for tab switching
        echo "<script>
        function showTab(tabName) {
            document.querySelectorAll('[id$=\"-tab\"]').forEach(tab => {
                tab.style.display = 'none';
            });
            document.getElementById(tabName + '-tab').style.display = 'block';
            document.querySelectorAll('.error-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');
        }
        function toggleRelatedFiles() {
            var list = document.getElementById('related-files-list');
            var btn = document.getElementById('related-files-btn');
            if (list.style.display === 'none' || list.style.display === '') {
                list.style.display = 'block';
                btn.innerHTML = btn.innerHTML.replace('Show', 'Hide').replace('⌄', '⌃');
            } else {
                list.style.display = 'none';
                btn.innerHTML = btn.innerHTML.replace('Hide', 'Show').replace('⌃', '⌄');
            }
        }
        </script>";

        echo "</div></body></html>";
        die();
    }

    /**
     * Advanced PHP syntax highlighting
     */
    private static function highlightPhpSyntax($code)
    {
        // Remove trailing whitespace but preserve leading whitespace
        $leadingSpace = '';
        if (preg_match('/^(\s+)/', $code, $matches)) {
            $leadingSpace = $matches[1];
        }
        $code = trim($code);

        if (empty($code)) {
            return $leadingSpace;
        }

        $code = htmlspecialchars($code, ENT_NOQUOTES);

        // Handle comments first (to avoid highlighting inside comments)
        $code = preg_replace('/(\/\/.*$)/', '<span class="php-comment">$1</span>', $code);
        $code = preg_replace('/(\/\*.*?\*\/)/s', '<span class="php-comment">$1</span>', $code);

        // Handle strings (single and double quotes)
        $code = preg_replace('/"([^"\\\\]*(\\\\.[^"\\\\]*)*)"/', '<span class="php-string">"$1"</span>', $code);
        $code = preg_replace("/'([^'\\\\]*(\\\\.[^'\\\\]*)*)'/", '<span class="php-string">\'$1\'</span>', $code);

        // Keywords
        $keywords = [
            'abstract','and','array','as',
            'break',
            'callable','case','catch','class','clone','const','continue',
            'declare','default','die','do',
            'echo','else','elseif','empty','enddeclare','endfor','endforeach','endif','endswitch','endwhile','eval','exit','extends',
            'final','finally','for','foreach','function','false',
            'global','goto', 'get',
            'if','implements','include','include_once','instanceof','insteadof','interface','isset',
            'list',
            'namespace','new','null',
            'or',
            'print','private','protected','public',
            'require','require_once','return',
            'static','switch',
            'throw','trait','try','true',
            'unset','use',
            'var',
            'while',
            'xor',
            'yield',
        ];

        foreach ($keywords as $keyword) {
            $code = preg_replace('/\b(' . preg_quote($keyword) . ')\b(?![^<]*>)/', '<span class="php-keyword">$1</span>', $code);
        }

        // Variables (but not inside already highlighted content)
        $code = preg_replace('/(\$[a-zA-Z_][a-zA-Z0-9_]*)(?![^<]*>)/', '<span class="php-variable">$1</span>', $code);

        // Object operators and array access
        $code = preg_replace('/(-&gt;)/', '<span class="php-operator">-></span>', $code);
        $code = preg_replace('/(::)/', '<span class="php-operator">::</span>', $code);

        // Numbers
        $code = preg_replace('/\b(\d+\.?\d*)\b(?![^<]*>)/', '<span class="php-number">$1</span>', $code);

        // Function calls (word followed by opening parenthesis, but not keywords)
        $code = preg_replace('/\b([a-zA-Z_][a-zA-Z0-9_]*)\s*(?=\()(?![^<]*>)(?!.*<span class="php-keyword">)/', '<span class="php-function">$1</span>', $code);

        // Constants (all caps words)
        $code = preg_replace('/\b([A-Z_][A-Z0-9_]{2,})\b(?![^<]*>)/', '<span class="php-constant">$1</span>', $code);

        // Operators
        $operators = [
            '=', '+', '-', '*', '/',
            '%', '==', '!=', '&lt;', '&gt;',
            '&lt;=', '&gt;=', '&amp;&amp;', '||', '!',
            '&amp;', '|', '^', '&lt;&lt;', '&gt;&gt;'
        ];
        foreach ($operators as $op) {
            $pattern = '/(' . preg_quote($op, '/') . ')(?![^<]*>)/';
            $code = preg_replace($pattern, '<span class="php-operator">$1</span>', $code);
        }

        return $leadingSpace . $code;
    }
}
