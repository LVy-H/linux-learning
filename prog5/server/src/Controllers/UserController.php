<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\User;

final class UserController
{
    public function index(): Response
    {
        $users = (new User())->all();
        return Response::html($this->render('users', ['users' => $users]));
    }

    public function show(string $id): Response
    {
        $user = (new User())->findById((int) $id);
        return Response::html($this->render('user-detail', ['user' => $user]));
    }

    private function render(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require __DIR__ . '/../Views/' . $view . '.php';
        return (string) ob_get_clean();
    }
}