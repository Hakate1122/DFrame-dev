<?php

use App\Middleware\UserAuthencation;
use Core\Application\View;

UserAuthencation::registerSelf();

$router = new Core\Application\Router();

$router->get('/', function () {
return View::render('app');
})->name('app');

$router->get('/app', function () {
return View::render('app');
})->name('app');

$router->default(function () {
    return get404pages() ?? '404 Not Found';
});

$router->scanControllerAttributes([
    '\App\Controller\DemoController',
    '\App\Controller\AnotherController',
    '\App\Controller\ThirdController',
]);