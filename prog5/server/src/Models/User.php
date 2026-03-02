<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User
{
    public function all(): array
    {
        $stmt = Database::connection()->query(
            'SELECT id, username, fullname, email, phone, role, created_at FROM users ORDER BY id ASC'
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, username, fullname, email, phone, role, created_at FROM users WHERE id = ?'
        );
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }
}