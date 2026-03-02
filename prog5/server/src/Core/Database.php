<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = require __DIR__ . '/../../config/config.php';
        $dbConfig = $config['db'];

        try {
            self::$connection = new PDO(
                "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}",
                $dbConfig['user'],
                $dbConfig['password']
            );
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }

        return self::$connection;
    }

    public static function initSchema(): void
    {
        $pdo = self::connection();

        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            fullname VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            phone VARCHAR(20),
            role ENUM('teacher', 'student') NOT NULL DEFAULT 'student',
            avatar_path VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $hasAvatarPath = $pdo->query("SHOW COLUMNS FROM users LIKE 'avatar_path'")->fetch();
        if (!$hasAvatarPath) {
            $pdo->exec("ALTER TABLE users ADD COLUMN avatar_path VARCHAR(255) NULL");
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            teacher_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            file_path VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS submissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            assignment_id INT NOT NULL,
            student_id INT NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $hasAssignmentId = $pdo->query("SHOW COLUMNS FROM submissions LIKE 'assignment_id'")->fetch();
        if (!$hasAssignmentId) {
            $hasExerciseId = $pdo->query("SHOW COLUMNS FROM submissions LIKE 'exercise_id'")->fetch();
            if ($hasExerciseId) {
                $pdo->exec("ALTER TABLE submissions ADD COLUMN assignment_id INT NULL");
                $pdo->exec("UPDATE submissions SET assignment_id = exercise_id WHERE assignment_id IS NULL");
            } else {
                $pdo->exec("ALTER TABLE submissions ADD COLUMN assignment_id INT NULL");
            }
        }

        $hasExerciseId = $pdo->query("SHOW COLUMNS FROM submissions LIKE 'exercise_id'")->fetch();
        if ($hasExerciseId) {
            $pdo->exec("UPDATE submissions SET assignment_id = exercise_id WHERE assignment_id IS NULL");
        }

        $hasNullAssignment = $pdo->query("SELECT 1 FROM submissions WHERE assignment_id IS NULL LIMIT 1")->fetch();
        if (!$hasNullAssignment) {
            $existingFk = $pdo->query(
                "SELECT CONSTRAINT_NAME
                 FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'submissions'
                   AND COLUMN_NAME = 'assignment_id'
                   AND REFERENCED_TABLE_NAME = 'assignments'
                 LIMIT 1"
            )->fetchColumn();

            if (!$existingFk) {
                $pdo->exec("ALTER TABLE submissions ADD CONSTRAINT fk_submissions_assignment FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE");
            }

            if ($hasExerciseId) {
                $legacyFk = $pdo->query(
                    "SELECT CONSTRAINT_NAME
                     FROM information_schema.KEY_COLUMN_USAGE
                     WHERE TABLE_SCHEMA = DATABASE()
                       AND TABLE_NAME = 'submissions'
                       AND COLUMN_NAME = 'exercise_id'
                       AND REFERENCED_TABLE_NAME IS NOT NULL
                     LIMIT 1"
                )->fetchColumn();

                if ($legacyFk) {
                    $pdo->exec("ALTER TABLE submissions DROP FOREIGN KEY `" . $legacyFk . "`");
                }

                $pdo->exec("ALTER TABLE submissions DROP COLUMN exercise_id");
            }

            $assignmentColumn = $pdo->query("SHOW COLUMNS FROM submissions LIKE 'assignment_id'")->fetch();
            if (is_array($assignmentColumn) && strtoupper((string)($assignmentColumn['Null'] ?? 'YES')) === 'YES') {
                $pdo->exec("ALTER TABLE submissions MODIFY assignment_id INT NOT NULL");
            }
        }

    }
}