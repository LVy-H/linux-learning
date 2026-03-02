<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
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
}
