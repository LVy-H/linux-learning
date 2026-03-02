<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Models\Message;

final class MessageController
{
    public function create(string $receiverId): Response
    {
        $senderId = (int) (Auth::id() ?? 0);
        $content = trim((string) ($_POST['content'] ?? ''));
        if ($content !== '') {
            (new Message())->create($senderId, (int) $receiverId, $content);
        }

        return Response::redirect('/users/' . (int) $receiverId);
    }

    public function update(string $id): Response
    {
        $senderId = (int) (Auth::id() ?? 0);
        $receiverId = (int) ($_POST['receiver_id'] ?? 0);
        $content = trim((string) ($_POST['content'] ?? ''));
        if ($content !== '') {
            (new Message())->updateOwned((int) $id, $senderId, $content);
        }

        return Response::redirect('/users/' . $receiverId);
    }

    public function delete(string $id): Response
    {
        $senderId = (int) (Auth::id() ?? 0);
        $receiverId = (int) ($_POST['receiver_id'] ?? 0);
        (new Message())->deleteOwned((int) $id, $senderId);

        return Response::redirect('/users/' . $receiverId);
    }
}
