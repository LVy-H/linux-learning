<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\AssignmentController;
use App\Controllers\HomeController;
use App\Controllers\MessageController;
use App\Core\Router;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;

return function (Router $router): void {
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/users', [UserController::class, 'index'], [[AuthMiddleware::class, 'requireLogin']]);
    $router->get('/users/{id}', [UserController::class, 'show'], [[AuthMiddleware::class, 'requireLogin']]);
    $router->get('/avatar/{id}', [UserController::class, 'avatar'], [[AuthMiddleware::class, 'requireLogin']]);

    $router->get('/teacher/students/new', [UserController::class, 'showCreateStudent'], [[AuthMiddleware::class, 'requireTeacher']]);
    $router->post('/teacher/students', [UserController::class, 'createStudent'], [[AuthMiddleware::class, 'requireTeacher']]);
    $router->get('/teacher/students/{id}/edit', [UserController::class, 'showEditStudent'], [[AuthMiddleware::class, 'requireTeacher']]);
    $router->post('/teacher/students/{id}/update', [UserController::class, 'updateStudent'], [[AuthMiddleware::class, 'requireTeacher']]);
    $router->post('/teacher/students/{id}/delete', [UserController::class, 'deleteStudent'], [[AuthMiddleware::class, 'requireTeacher']]);

    $router->get('/me', [UserController::class, 'me'], [[AuthMiddleware::class, 'requireStudent']]);
    $router->post('/me/update', [UserController::class, 'updateMe'], [[AuthMiddleware::class, 'requireStudent']]);

    $router->post('/users/{id}/messages', [MessageController::class, 'create'], [[AuthMiddleware::class, 'requireLogin']]);
    $router->post('/messages/{id}/update', [MessageController::class, 'update'], [[AuthMiddleware::class, 'requireLogin']]);
    $router->post('/messages/{id}/delete', [MessageController::class, 'delete'], [[AuthMiddleware::class, 'requireLogin']]);

    $router->get('/assignments', [AssignmentController::class, 'index'], [[AuthMiddleware::class, 'requireLogin']]);
    $router->post('/teacher/assignments', [AssignmentController::class, 'create'], [[AuthMiddleware::class, 'requireTeacher']]);
    $router->get('/assignments/{id}/download', [AssignmentController::class, 'download'], [[AuthMiddleware::class, 'requireLogin']]);
    $router->post('/assignments/{id}/submit', [AssignmentController::class, 'submit'], [[AuthMiddleware::class, 'requireStudent']]);
    $router->get('/teacher/submissions', [AssignmentController::class, 'submissions'], [[AuthMiddleware::class, 'requireTeacher']]);
    $router->get('/teacher/submissions/{id}/download', [AssignmentController::class, 'downloadSubmission'], [[AuthMiddleware::class, 'requireTeacher']]);

    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->post('/logout', [AuthController::class, 'logout'], [[AuthMiddleware::class, 'requireLogin']]);
};