<?php

use App\Controller\UserController;

use App\Middleware\UserAuthencation;

UserAuthencation::registerSelf();

$router = new DFrame\Application\Router();

$router->get('/', function () {
    return "Hello, World!";
})->name('home');

$router->get('/user/list', [UserController::class, 'listUsers'])->name('user.list');
$router->get('/user/store', [UserController::class, 'addUser'])->name('user.add');
$router->post('/user/store', [UserController::class, 'storeUser'])->name('user.store');


$router->default(function () {
    return get404pages() ?? '404 Not Found';
});
