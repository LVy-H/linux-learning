<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    private static ?array $cachedUser = null;

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['SERVER_PORT'] ?? null) === '443');
            session_set_cookie_params([
                'httponly' => true,
                'secure' => $isHttps,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function id(): ?int
    {
        self::start();
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function user(): ?array
    {
        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        $id = self::id();
        if ($id === null) {
            return null;
        }

        self::$cachedUser = (new User())->findById($id);
        return self::$cachedUser;
    }

    public static function isLoggedIn(): bool
    {
        return self::id() !== null;
    }

    public static function isTeacher(): bool
    {
        $user = self::user();
        return $user !== null && $user['role'] === 'teacher';
    }

    public static function isStudent(): bool
    {
        $user = self::user();
        return $user !== null && $user['role'] === 'student';
    }
}
