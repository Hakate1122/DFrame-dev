<?php

declare(strict_types=1);

/*
| Basic Configuration
|--------------------------------------------------------------------------
| This file serves as the entry point for the DLight web application.
| It sets up the environment, handles autoloading, and initializes the application.
*/

if (PHP_SAPI === 'cli') {
    die("'index.php' is meant for web server context. Use 'cli' for command line interface.\n");
}

ob_start();
define('D_RUN', microtime(true));

/*
| Define ROOT_DIR (Base of the framework)
|------------------------------------------------------------------------------------------------
| This defines the root directory of the application.
| It checks if the directory exists and is readable.
| If not, it returns a 500 error.
|------------------------------------------------------------------------------------------------
*/
if (!defined('ROOT_DIR')) {
    $rootDir = dirname(__DIR__) . DIRECTORY_SEPARATOR;
    if (!is_dir($rootDir) || !is_readable($rootDir)) {
        http_response_code(500);
        die('Application root directory not accessible');
    }
    /** Define the root directory constant of DLight application */
    define('ROOT_DIR', $rootDir);
}

/*
| Define INDEX_DIR - Base of entry file (index.php)
|------------------------------------------------------------------------------------------------
| This defines the index directory of the application.
| It checks if the directory exists and is readable.
| If not, it returns a 500 error.
|------------------------------------------------------------------------------------------------
*/
if (!defined('INDEX_DIR')) {
    $indexDir = __DIR__ . DIRECTORY_SEPARATOR;
    if (!is_dir($indexDir) || !is_readable($indexDir)) {
        http_response_code(500);
        die('Index directory not accessible');
    }
    /** Define the index directory constant of DLight application */
    define('INDEX_DIR', $indexDir);
}

/*
| Autoloading
|------------------------------------------------------------------------------------------------
| This loads the Composer autoloader to include all dependencies.
| If the autoloader is not found, it returns a 500 error.
|------------------------------------------------------------------------------------------------
*/
$autoloadFile = ROOT_DIR . '/vendor/autoload.php';
if (!file_exists($autoloadFile)) {
    http_response_code(500);
    die(file_get_contents('source/miss.html') ?: 'Autoloader not found. Please run <code>composer install</code>.');
}
require_once $autoloadFile;

/*
| Set Maintenance Mode if needed (localhost will be bypassed)
|------------------------------------------------------------------------------------------------
| This checks the environment variable to determine if maintenance mode should be enabled.
| **NOTE**: Maintenance mode will be bypassed for requests coming from localhost
|------------------------------------------------------------------------------------------------
*/

// \DLight\Application\App::setMaintenanceMode(true);

/*
| Initialize and boot the DLight web application
|------------------------------------------------------------------------------------------------
| This sets up the application environment and prepares it for web requests.
| After initialization, it boots the application to handle incoming requests.
|------------------------------------------------------------------------------------------------
*/

\DLight\Reports\Report::setup(true, INDEX_DIR . 'logs/app.log', \DLight\Reports\Report::html());

$app = new \DLight\Application\App();
$app->setUpWebRoutes(ROOT_DIR . 'app/Router/web/web.php')
    ->setUpWebRoutes(ROOT_DIR . 'app/Router/web/dcloud.php')
    ->setUpWebRoutes(ROOT_DIR . 'app/Router/web/user_basic_crud.php')

    ->setUpApiRoutes(ROOT_DIR . 'app/Router/api/api.php')
    ->setUpApiRoutes(ROOT_DIR . 'app/Router/api/api_product_basic.php')
    
    ->bootWeb();
