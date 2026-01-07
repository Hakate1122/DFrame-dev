<?php

use App\Middleware\UserAuthencation;
use DFrame\Application\View;

UserAuthencation::sign();

$router = new DFrame\Application\Router();

$router->sign('GET /', function () {
    return View::render('home');
})->name('home');

$router->sign('GET /game/air-balloon', function () {
    return View::render('game/air-balloon');
})->name('game.air-balloon');

$router->sign('GET /game/sudoku', function () {
    return View::render('game/sudoku');
})->name('game.sudoku');

$router->group('/demo')::action(function (DFrame\Application\Router $router) {
    $router->sign('GET /chat', function () {
        return View::render('demo/chat');
    })->name('demo.chat');

    $router->sign('GET /editor', function () {
        return View::render('demo/editor');
    })->name('demo.editor');
});


$router->sign('GET /demo/ws', function () {
    return View::render('demo/ws');
})->name('demo.ws');

$router->sign('GET /sitemap.xml', [\App\Controller\SitemapController::class, 'index'])->name('sitemap');

$router->default(function () {
    return get404pages() ?? '404 Not Found';
});

$router->scanControllerAttributes([
        App\Controller\UserController::class,
]);