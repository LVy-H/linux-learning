<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Core\View;
use App\Models\User;

final class AuthController
{
    public function showLogin(): Response
    {
        if (Auth::isLoggedIn()) {
            return Response::redirect('/users');
        }
        return Response::html(View::render('login', ['error_message' => '']));
    }

    public function login(): Response
    {
        Auth::start();

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $user = (new User())->findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            return Response::redirect('/users');
        }

        return Response::html(View::render('login', ['error_message' => 'Invalid credentials. Please try again.']), 401);
    }

    public function logout(): Response
    {
        Auth::start();
        session_unset();
        session_destroy();
        return Response::redirect('/login');
    }

}
