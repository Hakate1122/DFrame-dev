<?php

declare(strict_types=1);

namespace App\Controller;

use DFrame\Application\View;

class HomeController
{
    public function home()
    {
        return View::render('home', [
            'phpVersion' => phpversion(),
            'dframeVersion' => \DFrame\Application\App::version,
            'os' => PHP_OS,
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'memory' => memory_get_usage(true),
            'memoryLimit' => ini_get('memory_limit')
        ]);
    }
}
