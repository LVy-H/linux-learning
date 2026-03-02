<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Submission
{
    public function create(int $assignmentId, int $studentId, string $filePath): bool
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO submissions (assignment_id, student_id, file_path) VALUES (?, ?, ?)'
        );
        return $stmt->execute([$assignmentId, $studentId, $filePath]);
    }

    public function allForTeacher(): array
    {
        $pdo = Database::connection();
        $hasAssignmentId = $pdo->query("SHOW COLUMNS FROM submissions LIKE 'assignment_id'")->fetch();
        $joinColumn = $hasAssignmentId ? 'assignment_id' : 'exercise_id';

        $stmt = $pdo->query(
            'SELECT s.id, s.file_path, s.created_at,
                    a.id AS assignment_id, a.title AS assignment_title,
                    u.id AS student_id, u.fullname AS student_name, u.username AS student_username
             FROM submissions s
             JOIN assignments a ON a.id = s.' . $joinColumn . '
             JOIN users u ON u.id = s.student_id
             ORDER BY s.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM submissions WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
