<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;

final class HomeController
{
    public function index(): Response
    {
        ob_start();
        require __DIR__ . '/../Views/home.php';
        return Response::html((string) ob_get_clean());
    }
}
