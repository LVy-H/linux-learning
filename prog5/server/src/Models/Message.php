<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Message
{
    public function forReceiver(int $receiverId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT m.id, m.content, m.sender_id, m.receiver_id, m.created_at, m.updated_at, u.username AS sender_username
             FROM messages m
             JOIN users u ON u.id = m.sender_id
             WHERE m.receiver_id = ?
             ORDER BY m.created_at DESC'
        );
        $stmt->execute([$receiverId]);
        return $stmt->fetchAll();
    }

    public function create(int $senderId, int $receiverId, string $content): bool
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)'
        );
        return $stmt->execute([$senderId, $receiverId, $content]);
    }

    public function updateOwned(int $messageId, int $senderId, string $content): bool
    {
        $stmt = Database::connection()->prepare(
            'UPDATE messages SET content = ?, updated_at = NOW() WHERE id = ? AND sender_id = ?'
        );
        return $stmt->execute([$content, $messageId, $senderId]);
    }

    public function deleteOwned(int $messageId, int $senderId): bool
    {
        $stmt = Database::connection()->prepare('DELETE FROM messages WHERE id = ? AND sender_id = ?');
        return $stmt->execute([$messageId, $senderId]);
    }
}
