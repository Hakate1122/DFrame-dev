<?php

use App\Controller\UserController;

use DFrame\Application\Router;

Router::group('/user')::action(function (Router $router) {
    $router->sign('GET /list', [UserController::class, 'listUsers'])->name('user.list');
    $router->sign('GET /store', [UserController::class, 'addUser'])->name('user.add');
    $router->sign('POST /store', [UserController::class, 'storeUser'])->name('user.store');
    $router->sign('GET /edit/{id}', [UserController::class, 'editUser'])->name('user.edit');
    $router->sign('POST /edit/{id}', [UserController::class, 'updateUser'])->name('user.update');
    $router->sign('DELETE /delete/{id}', [UserController::class, 'deleteUser'])->name('user.delete');
});