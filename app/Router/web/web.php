<?php

use App\Middleware\UserAuthencation;
use DFrame\Application\View;

UserAuthencation::sign();

$router = new DFrame\Application\Router();

$router->sign('GET /', [\App\Controller\HomeController::class, 'home'])->name('home');

$router->sign('GET /ws/chat', function () {
    return View::render('ws/chat');
})->name('ws.chat');

$router->sign('GET /sitemap.xml', [\App\Controller\SitemapController::class, 'index'])->name('sitemap');

$router->scanControllerAttributes([
        App\Controller\UserController::class,
]);