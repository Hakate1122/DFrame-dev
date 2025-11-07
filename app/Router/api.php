<?php

use App\Controller\OrderController;

$router = new DFrame\Application\Router();

$router->apiGet('/orders', [OrderController::class,'getOrders']);
$router->apiGet('/demo', 'App\Controller\OrderController@createOrder');
$router->apiGet('/demos', 'App\Controller\OrderController@createOrder');