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
            'SELECT id, username, fullname, email, phone, role, avatar_path, created_at FROM users WHERE id = ?'
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

    public function createStudent(array $data): bool
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO users (username, password, fullname, email, phone, role) VALUES (?, ?, ?, ?, ?, ?)' 
        );

        return $stmt->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['fullname'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            'student',
        ]);
    }

    public function updateByTeacher(int $id, array $data): bool
    {
        $fields = ['email = ?', 'phone = ?'];
        $values = [
            $data['email'] ?? null,
            $data['phone'] ?? null,
        ];

        if (!empty($data['username'])) {
            $fields[] = 'username = ?';
            $values[] = $data['username'];
        }
        if (!empty($data['fullname'])) {
            $fields[] = 'fullname = ?';
            $values[] = $data['fullname'];
        }
        if (!empty($data['password'])) {
            $fields[] = 'password = ?';
            $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $values[] = $id;
        $stmt = Database::connection()->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ? AND role = "student"');
        return $stmt->execute($values);
    }

    public function deleteStudent(int $id): bool
    {
        $stmt = Database::connection()->prepare('DELETE FROM users WHERE id = ? AND role = "student"');
        return $stmt->execute([$id]);
    }

    public function updateStudentSelf(int $id, array $data): bool
    {
        $fields = ['email = ?', 'phone = ?'];
        $values = [
            $data['email'] ?? null,
            $data['phone'] ?? null,
        ];

        if (!empty($data['password'])) {
            $fields[] = 'password = ?';
            $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (!empty($data['avatar_path'])) {
            $fields[] = 'avatar_path = ?';
            $values[] = $data['avatar_path'];
        }

        $values[] = $id;
        $stmt = Database::connection()->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ? AND role = "student"');
        return $stmt->execute($values);
    }
}