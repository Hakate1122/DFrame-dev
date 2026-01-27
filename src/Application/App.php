<?php

namespace DFrame\Application;

use DFrame\Application\Router;
use DFrame\Application\Session;
use DFrame\Command\Register;
use Datahihi1\TinyEnv\TinyEnv;
use Exception;

/**
 * **Application Core**
 *
 * This class initializes and boot the application environment, sets up error handling,
 * loads environment variables, and configures reporting for errors, exceptions,
 * and runtime issues. It also handles reporting if the application is run
 * from the command line.
 */
class App
{
    /**
     * Version of DFrame Framework.
     * @var string
     */
    public const VERSION = '20260128-dev';
    /**
     * Alias for version constant
     */
    public const version = self::VERSION;
    /**
     * Application environment
     * @var string
     */
    private static $environment = 'production';

    /**
     * Application debug mode (default true for development)
     * @var bool
     */
    private static $debug = false;

    /**
     * Whether the application is running from a PHAR archive
     * @var bool
     */
    private static $runningFromPhar = false;

    /**
     * Flags to track if web routes have been loaded
     * @var bool
     */
    private bool $webRoutesLoaded = false;

    /**
     * Flag to track if API routes have been loaded
     * @var bool
     */
    private bool $apiRoutesLoaded = false;

    /**
     * Flag to track if DLI routes have been loaded
     * @var bool
     */
    private bool $dliRoutesLoaded = false;

    /**
     * Optional stored path for DLI/command routes when set via `setUpDliRoutes()`.
     * @var string|null
     */
    private ?string $dliRoutesPath = null;

    /**
     * Constructor for App class to determine ROOT_DIR and INDEX_DIR
     *
     * @throws Exception if ROOT_DIR or INDEX_DIR are not defined
     */
    public function __construct()
    {

        if (php_sapi_name() === 'cli' || PHP_SAPI === 'cli') {
            if (!defined('ROOT_DIR')) {
                throw new Exception('ROOT_DIR must be defined before initializing CraftPHP Framework (CLI mode).');
            }
            return;
        }

        if (!defined('ROOT_DIR') || !defined('INDEX_DIR')) {
            throw new Exception('ROOT_DIR and INDEX_DIR must be defined before initializing CraftPHP Framework.');
        }
    }

    /**
     * Initialize application configuration
     */
    private static function initializeConfig(): void
    {
        self::$environment = env('APP_ENVIRONMENT', 'production');
        self::$debug = env('APP_DEBUG', 'false');

        if (!in_array(self::$environment, ['local', 'development', 'staging', 'production'])) {
            self::$environment = 'production';
        }

        if (self::$environment === 'production') {
            self::$debug = false;
        }
    }

    /**
     * Get current environment
     */
    public static function environment(): string
    {
        return self::$environment;
    }

    /**
     * Check if application is in debug mode
     */
    public static function isDebug(): bool
    {
        return self::$debug;
    }

    /**
     * Check if running from a PHAR archive
     */
    public static function isRunningFromPhar(): bool
    {
        return self::$runningFromPhar;
    }

    /**
     * Check if application is in production mode
     */
    public static function isProduction(): bool
    {
        return self::$environment === 'production';
    }

    /**
     * Set security headers for web requests
     */
    private static function setSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');

        if (self::isProduction()) {
            header(
                "Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';"
            );
        }

        header_remove('X-Powered-By');
    }

    /**
     * Set maintenance mode if enabled in environment variables
     * @param bool $noEnv Not read from env, always enable maintenance
     */
    public static function setMaintenanceMode($noEnv = false): void
    {
        date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));
        if (headers_sent()) {
            return;
        }

        $serverIps = ['127.0.0.1', '::1'];
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';

        if (in_array($clientIp, $serverIps)) {
            return;
        }

        if ($noEnv === true) {
            $maintenanceMode = true;
            $startTime = null;
            $endTime = null;
        } else {
            $maintenanceMode = env('MAINTENANCE_MODE', 'false');
            $startTime = env('MAINTENANCE_START_TIME', null);
            $endTime = env('MAINTENANCE_END_TIME', null);
        }
        $currentTime = time();

        if ($endTime && $currentTime > (int) $endTime) {
            return;
        }

        if (filter_var($maintenanceMode, FILTER_VALIDATE_BOOLEAN)) {
            if (php_sapi_name() === 'cli' || PHP_SAPI === 'cli') {
                echo "The application is currently under maintenance. Please try again later.\n";
                exit();
            }
            header('HTTP/1.1 503 Service Unavailable');
            header('Retry-After: 3600');
            $startStr = $startTime ? date('H:i:s d/m/Y', (int) $startTime) : null;
            $endStr = $endTime ? date('H:i:s d/m/Y', (int) $endTime) : null;
            $countdown = ($endTime && $currentTime < (int) $endTime) ? ((int) $endTime - $currentTime) : null;

            if (file_exists(INDEX_DIR . 'maintenance.php')) {
                echo str_replace(
                    ['{start}', '{end}', '{countdown}'],
                    [$startStr ?? '', $endStr ?? '', $countdown ?? ''],
                    file_get_contents(INDEX_DIR . 'maintenance.php')
                );
            } else {
                echo '<h1>Maintenance Mode</h1>';
                if ($startStr) {
                    echo "<p>Start: $startStr</p>";
                }
                if ($endStr) {
                    echo "<p>End: $endStr</p>";
                    if ($countdown) {
                        $hours = floor($countdown / 3600);
                        $minutes = floor(($countdown % 3600) / 60);
                        $seconds = $countdown % 60;
                        echo "<p>Remaining: {$hours}h {$minutes}m {$seconds}s</p>";
                    }
                }
                echo '<p>The site is currently under maintenance. Please check back later.</p>';
            }
            exit();
        }
    }

    /**
     * Validate session configuration
     *
     * @return void
     * @throws Exception
     */
    private static function validateSessionConfig()
    {
        if (self::isProduction()) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_secure', '1');
            ini_set('session.use_strict_mode', '1');
            ini_set('session.cookie_same_site', 'Strict');
        }

        $sessionPath = ini_get('session.save_path');
        if ($sessionPath && !is_writable($sessionPath)) {
            throw new Exception('Session save path is not writable: ' . $sessionPath);
        }
    }

    /**
     * Validate service configuration
     *
     * @return void
     * @throws Exception
     */
    private static function validateServiceConfig()
    {
        $requiredVars = ['APP_NAME', 'APP_TIMEZONE'];
        foreach ($requiredVars as $var) {
            if (!env($var)) {
                throw new Exception("Required environment variable missing: {$var}");
            }
        }
    }

    /**
     * Check if running from a PHAR archive
     *
     * @return bool
     */
    private static function checkRunningFromPhar()
    {
        $pharRunning = false;
        try {
            if (class_exists('Phar') && \Phar::running(false) !== '') {
                $pharRunning = true;
            }
        } catch (\Throwable $t) {
            // ignore
        }

        if (!$pharRunning) {
            $a0 = $_SERVER['argv'][0] ?? '';
            if (is_string($a0) && (str_contains($a0, '.phar') || str_contains($a0, 'phar://'))) {
                $pharRunning = true;
            }
        }

        if (!$pharRunning) {
            $selfPath = __FILE__;
            if (is_string($selfPath) && str_starts_with($selfPath, 'phar://')) {
                $pharRunning = true;
            }
        }
        return $pharRunning;
    }

    /**
     * Validate application health
     *
     * @return array
     */
    private static function healthCheck(): array
    {
        $health = [
            'status' => 'healthy',
            'environment' => self::$environment,
            'debug' => self::$debug,
            'version' => self::version,
            'timestamp' => date('Y-m-d H:i:s'),
            'checks' => []
        ];

        $requiredDirs = [
            'logs' => INDEX_DIR . 'logs/',
            'vendor' => ROOT_DIR . 'vendor/',
            'app' => ROOT_DIR . 'app/'
        ];

        foreach ($requiredDirs as $name => $path) {
            $health['checks'][$name] = [
                'status' => is_dir($path) && is_readable($path) ? 'ok' : 'error',
                'path' => $path
            ];
        }

        foreach ($health['checks'] as $check) {
            if ($check['status'] === 'error') {
                $health['status'] = 'unhealthy';
                break;
            }
        }

        return $health;
    }

    /**
     * Load environment variables from .env file
     *
     * @return void
     */
    private static function loadEnvironmentVariables()
    {
        if (!class_exists(TinyEnv::class)) {
            throw new Exception('TinyEnv is not installed. Please run "composer require datahihi1/tiny-env"');
        }
        $env = new TinyEnv(ROOT_DIR);
        $env->envfiles(['.env', '.env.encrypted']);
        $env->load();
    }

    /**
     * Configure error reporting based on environment
     *
     * @return void
     */
    private static function configureErrorReporting()
    {
        if (self::$environment === 'production') {
            error_reporting(0);
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
            ini_set('log_errors', '1');
            ini_set('error_log', INDEX_DIR . 'logs/php_errors.log');

            set_exception_handler(function ($e) {
                if (!headers_sent()) {
                    http_response_code(500);
                }
                echo function_exists('get500pages') ? get500pages() : 'Internal Server Error';
                if (is_object($e) || is_string($e)) {
                    error_log((string) $e);
                }
                exit(1);
            });

            set_error_handler(function ($severity, $message, $file, $line) {
                if (!headers_sent()) {
                    http_response_code(500);
                }
                echo function_exists('get500pages') ? get500pages() : 'Internal Server Error';
                error_log("PHP Error: [{$severity}] {$message} in {$file} on line {$line}");
                exit(1);
            });

            register_shutdown_function(function () {
                $err = error_get_last();
                if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
                    if (!headers_sent()) {
                        http_response_code(500);
                    }
                    $path = ROOT_DIR . 'src/Kit/helper/default_pages.php';
                    echo function_exists('get500pages') ? get500pages() : 'Internal Server Error';
                }
            });
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
        }
    }

    /**
     * Configure timezone from environment variable
     *
     * @return void
     */
    private static function configureTimezone()
    {
        $timezone = env('APP_TIMEZONE', 'UTC');
        if (!in_array($timezone, \DateTimeZone::listIdentifiers())) {
            $timezone = 'UTC';
        }
        date_default_timezone_set($timezone);
    }

    /**
     * Initializes the routing configuration (instance-aware).
     * Will only include files that haven't been loaded via `setUp*Routes()`.
     *
     * @return void
     */
    private function initializeRoute()
    {
        $routeConfigPath = ROOT_DIR . 'app/Router/web.php';
        $apiRouteConfigPath = ROOT_DIR . 'app/Router/api.php';

        if (!$this->webRoutesLoaded && file_exists($routeConfigPath)) {
            require $routeConfigPath;
            $this->webRoutesLoaded = true;
        }

        if (!$this->apiRoutesLoaded && file_exists($apiRouteConfigPath)) {
            require $apiRouteConfigPath;
            $this->apiRoutesLoaded = true;
        }
    }

    /**
     * Include a web routes file and mark web routes as loaded.
     *
     * @param string|null $logDir The directory where log files will be stored.
     *
     * @return self
     */
    public function setUpWebRoutes(string $path): self
    {
        if (file_exists($path)) {
            require $path;
            $this->webRoutesLoaded = true;
        }
        return $this;
    }

    /**
     * Include an api routes file and mark api routes as loaded.
     */
    public function setUpApiRoutes(string $path): self
    {
        if (file_exists($path)) {
            require $path;
            $this->apiRoutesLoaded = true;
        }
        return $this;
    }

    /**
     * Include a CLI/command routes file and mark dli routes as loaded.
     */
    public function setUpDliRoutes(string $path): self
    {
        $this->dliRoutesPath = $path;
        $this->dliRoutesLoaded = false;
        return $this;
    }
    /**
     * Initializes the environment.
     *
     * @return self
     */
    public static function initialize()
    {
        try {

            self::$runningFromPhar = self::checkRunningFromPhar();

            // Load environment files (.env, encrypted .env if present)
            self::loadEnvironmentVariables();

            // Initialize configuration
            self::initializeConfig();

            // Configure error reporting
            self::configureErrorReporting();

            // Configure timezone
            self::configureTimezone();

            // Validate session configuration
            self::validateSessionConfig();

            // Validate required environment variables for services
            self::validateServiceConfig();

            // Perform health check (optional, can log or act on results)
            $healthStatus = self::healthCheck();
            if ($healthStatus['status'] === 'unhealthy') {
                dump($healthStatus);
                throw new Exception('Application health check failed.');
            }
        } catch (Exception $e) {
            if (self::isDebug()) {
                throw $e;
            }
        }

        return new self();
    }

    /**
     * Boots the web application.
     *
     * @param array|null $argv Optional argv (kept for compatibility)
     * @return void
     */
    public function bootWeb()
    {

        // Set maintenance mode if enabled
        self::setMaintenanceMode();

        // Start session
        Session::start();

        // Set CORS headers for API requests
        setApiCorsHeaders();

        // // Set security headers
        // self::setSecurityHeaders();

        // Start run route handler (only include files not already loaded)
        $this->initializeRoute();

        Router::run();
    }

    /**
     * Starts DLI application.
     *
     * @param array $argv
     * @return void
     */
    public function bootDli($argv)
    {

        // Kernel
        $cli = new Command();

        // Load core commands
        (new Register())->core($cli);

        if ($this->dliRoutesPath) {
            if (file_exists($this->dliRoutesPath)) {
                require_once $this->dliRoutesPath;
                $this->dliRoutesLoaded = true;
            }
        }

        // Boot the CLI application
        $cli->run($argv);
    }
}
