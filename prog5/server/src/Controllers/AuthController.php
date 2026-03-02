<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\View;
use App\Models\User;

final class AuthController
{
    public function showLogin(): Response
    {
        return Response::html(View::render('login', ['error_message' => '']));
    }

    public function login(): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $user = (new User())->findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = ($user['role'] === 'teacher');
            header('Location: /users');
            return Response::html('');
        }

        return Response::html(View::render('login', ['error_message' => 'Invalid credentials. Please try again.']), 401);
    }

    public function logout(): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header('Location: /login');
        return Response::html('');
    }

}
