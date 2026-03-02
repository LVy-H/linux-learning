<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\View;
use App\Models\User;

final class UserController
{
    public function index(): Response
    {
        $users = (new User())->all();
        return Response::html(View::render('users', ['users' => $users]));
    }

    public function show(string $id): Response
    {
        $user = (new User())->findById((int) $id);
        return Response::html(View::render('user-detail', ['user' => $user]));
    }
}