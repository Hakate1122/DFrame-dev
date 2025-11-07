<?php

use App\Middleware\UserAuthencation;
use DFrame\Application\View;

UserAuthencation::registerSelf();

$router = new DFrame\Application\Router();

$router->get('/', function () {
return View::render('app');
})->name('home');

$router->get('/app', function () {
return View::render('app');
})->name('app');

$router->default(function () {
    return get404pages() ?? '404 Not Found';
});

$router->scanControllerAttributes([
    \App\Controller\DemoController::class
]);