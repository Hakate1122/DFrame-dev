<?php

declare(strict_types=1);

use App\Middleware\UserAuthencation;
use DLight\Application\Router;

UserAuthencation::sign();

// DCloud - simple local file manager UI + API
Router::group('/dcloud')::action(function (Router $router) {
    $router->sign('GET /', [\App\Controller\DCloudController::class, 'index'])->name('dcloud.home');
    $router->sign('GET /api/list', [\App\Controller\DCloudController::class, 'listFiles'])->name('dcloud.list');
    $router->sign('POST /api/upload', [\App\Controller\DCloudController::class, 'upload'])->name('dcloud.upload');
    $router->sign('POST /api/delete', [\App\Controller\DCloudController::class, 'delete'])->name('dcloud.delete');
    $router->sign('POST /api/rename', [\App\Controller\DCloudController::class, 'rename'])->name('dcloud.rename');
});

// DImage - simple image hosting UI + API

// DMedia - simple media hosting UI + API

// DMusic - simple music hosting UI + API

// DPod - simple podcast hosting UI + API
