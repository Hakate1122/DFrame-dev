<?php

use App\Middleware\UserAuthencation;

UserAuthencation::registerSelf();

$router = new DFrame\Application\Router();

$router->get('/', function () {
    return "Trang chủ";
})->name('home');

$router->default(function () {
    return get404pages() ?? '404 Not Found';
});
