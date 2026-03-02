<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Assignment
{
    public function all(): array
    {
        $stmt = Database::connection()->query(
            'SELECT a.id, a.title, a.description, a.file_path, a.created_at, u.fullname AS teacher_name
             FROM assignments a
             JOIN users u ON u.id = a.teacher_id
             ORDER BY a.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM assignments WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(int $teacherId, string $title, string $description, string $filePath): bool
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO assignments (teacher_id, title, description, file_path) VALUES (?, ?, ?, ?)'
        );
        return $stmt->execute([$teacherId, $title, $description, $filePath]);
    }
}
