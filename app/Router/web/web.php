<?php

use App\Middleware\UserAuthencation;
use DFrame\Application\View;

UserAuthencation::sign();

$router = new DFrame\Application\Router();

$router->sign('GET /', [\App\Controller\HomeController::class, 'home'])->name('home');
$router->sign('GET /h', function () {
    echo "<img src='" . asset('images/logo.png') . "' alt='Logo'>";
})->name('home.alias');

$router->sign('GET /morse', function () {
    return View::render('morse');
})->name('morse');

$router->sign('GET /ws/chat', function () {
    return View::render('ws/chat');
})->name('ws.chat');

$router->sign('GET /sitemap.xml', [\App\Controller\SitemapController::class, 'index'])->name('sitemap');

$router->scanControllerAttributes([
        App\Controller\UserController::class,
]);