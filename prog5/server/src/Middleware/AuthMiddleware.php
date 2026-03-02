<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Response;

final class AuthMiddleware
{
    public static function requireLogin(): ?Response
    {
        if (!Auth::isLoggedIn()) {
            return Response::redirect('/login');
        }

        return null;
    }

    public static function requireTeacher(): ?Response
    {
        if (!Auth::isLoggedIn()) {
            return Response::redirect('/login');
        }

        if (!Auth::isTeacher()) {
            return Response::html('Forbidden', 403);
        }

        return null;
    }

    public static function requireStudent(): ?Response
    {
        if (!Auth::isLoggedIn()) {
            return Response::redirect('/login');
        }

        if (!Auth::isStudent()) {
            return Response::html('Forbidden', 403);
        }

        return null;
    }

    public static function requireCsrf(): ?Response
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return null;
        }

        $token = isset($_POST['_csrf_token']) ? (string) $_POST['_csrf_token'] : null;
        if (!Csrf::validate($token)) {
            return Response::html('Invalid CSRF token', 419);
        }

        return null;
    }
}
