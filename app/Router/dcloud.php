<?php

use App\Middleware\UserAuthencation;
use DFrame\Application\View;

UserAuthencation::sign();

$router = new DFrame\Application\Router();

$router->sign('GET /dcloud', function () {
    return View::render('DCloud/index');
})->name('dcloud');

