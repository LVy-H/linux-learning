<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Core\Router;
use App\Controllers\UserController;

return function (Router $router): void {
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/users', [UserController::class, 'index']);
    $router->get('/users/{id}', [UserController::class, 'show']);

    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->post('/logout', [AuthController::class, 'logout']);
};