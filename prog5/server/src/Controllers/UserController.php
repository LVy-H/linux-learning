<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Core\View;
use App\Models\Message;
use App\Models\User;

final class UserController
{
    public function index(): Response
    {
        $users = (new User())->all();
        return Response::html(View::render('users', [
            'users' => $users,
            'currentUser' => Auth::user(),
        ]));
    }

    public function show(string $id): Response
    {
        $currentUser = Auth::user();
        $user = (new User())->findById((int) $id);
        if (!$user) {
            return Response::html(View::render('user-detail', [
                'user' => null,
                'messages' => [],
                'currentUser' => $currentUser,
            ]), 404);
        }

        $isOwner = (int) ($currentUser['id'] ?? 0) === (int) $id;

        $messages = (new Message())->forReceiver((int) $id);
        $messages = array_filter($messages, function ($msg) use ($isOwner) {
            return $isOwner || (int) $msg['sender_id'] === (int) Auth::id();
        });
        return Response::html(View::render('user-detail', [
            'user' => $user,
            'messages' => $messages,
            'currentUser' => $currentUser,
        ]));
    }

    public function showCreateStudent(): Response
    {
        return Response::html(View::render('student-form', [
            'mode' => 'create',
            'student' => null,
            'error' => '',
        ]));
    }

    public function createStudent(): Response
    {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $fullname = trim((string) ($_POST['fullname'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));

        if ($username === '' || $password === '' || $fullname === '') {
            return Response::html(View::render('student-form', [
                'mode' => 'create',
                'student' => null,
                'error' => 'Username, password, and full name are required.',
            ]), 422);
        }

        $ok = (new User())->createStudent([
            'username' => $username,
            'password' => $password,
            'fullname' => $fullname,
            'email' => $email,
            'phone' => $phone,
        ]);

        if (!$ok) {
            return Response::html(View::render('student-form', [
                'mode' => 'create',
                'student' => null,
                'error' => 'Could not create student. Username may already exist.',
            ]), 422);
        }

        return Response::redirect('/users');
    }

    public function showEditStudent(string $id): Response
    {
        $student = (new User())->findById((int) $id);
        if (!$student || $student['role'] !== 'student') {
            return Response::html('Student not found', 404);
        }

        return Response::html(View::render('student-form', [
            'mode' => 'edit',
            'student' => $student,
            'error' => '',
        ]));
    }

    public function updateStudent(string $id): Response
    {
        $ok = (new User())->updateByTeacher((int) $id, [
            'username' => trim((string) ($_POST['username'] ?? '')),
            'password' => (string) ($_POST['password'] ?? ''),
            'fullname' => trim((string) ($_POST['fullname'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
        ]);

        if (!$ok) {
            return Response::html('Could not update student', 422);
        }

        return Response::redirect('/users/' . (int) $id);
    }

    public function deleteStudent(string $id): Response
    {
        (new User())->deleteStudent((int) $id);
        return Response::redirect('/users');
    }

    public function me(): Response
    {
        return Response::html(View::render('profile', [
            'user' => Auth::user(),
            'error' => '',
            'success' => '',
        ]));
    }

    public function updateMe(): Response
    {
        $current = Auth::user();
        if (!$current) {
            return Response::html('Unauthorized', 401);
        }

        $avatarPath = null;
        if (isset($_FILES['avatar_file']) && (int)($_FILES['avatar_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $avatarPath = $this->storeUploadedFile($_FILES['avatar_file'], 'avatars');
        } elseif (!empty($_POST['avatar_url'])) {
            $avatarPath = $this->downloadAvatarFromUrl((string) $_POST['avatar_url'], $current['id']);
        }

        $ok = (new User())->updateStudentSelf((int) $current['id'], [
            'password' => (string) ($_POST['password'] ?? ''),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'avatar_path' => $avatarPath,
        ]);

        if (!$ok) {
            return Response::html(View::render('profile', [
                'user' => (new User())->findById((int)$current['id']),
                'error' => 'Could not update profile.',
                'success' => '',
            ]), 422);
        }

        return Response::html(View::render('profile', [
            'user' => (new User())->findById((int)$current['id']),
            'error' => '',
            'success' => 'Profile updated.',
        ]));
    }

    public function avatar(string $id): Response
    {
        $user = (new User())->findById((int) $id);
        if (!$user || empty($user['avatar_path'])) {
            return Response::html('Not found', 404);
        }

        $path = realpath(__DIR__ . '/../../' . ltrim($user['avatar_path'], '/'));
        if ($path === false || !is_file($path)) {
            return Response::html('Not found', 404);
        }

        $mime = mime_content_type($path) ?: 'application/octet-stream';
        return new Response(file_get_contents($path) ?: '', 200, ['Content-Type' => $mime]);
    }

    private function storeUploadedFile(array $file, string $folder): ?string
    {
        $config = require __DIR__ . '/../../config/config.php';
        $base = rtrim($config['upload_dir'], '/');
        $targetDir = $base . '/' . $folder;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $ext = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return null;
        }

        $name = uniqid('upload_', true) . '.' . $ext;
        $fullPath = $targetDir . '/' . $name;
        if (!move_uploaded_file((string) $file['tmp_name'], $fullPath)) {
            return null;
        }

        return 'storage/uploads/' . $folder . '/' . $name;
    }

    private function downloadAvatarFromUrl(string $url, int $userId): ?string
    {
        if (!$this->isAllowedAvatarUrl($url)) {
            return null;
        }

        $context = stream_context_create([
            'http' => ['timeout' => 10],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $raw = @file_get_contents($url, false, $context);
        if ($raw === false || strlen($raw) > 5 * 1024 * 1024) {
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($raw) ?: '';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
            return null;
        }

        $config = require __DIR__ . '/../../config/config.php';
        $base = rtrim($config['upload_dir'], '/');
        $targetDir = $base . '/avatars';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $pathPart = parse_url($url, PHP_URL_PATH) ?: '';
        $ext = strtolower(pathinfo($pathPart, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            $ext = 'jpg';
        }

        $name = 'avatar_' . $userId . '_' . time() . '.' . $ext;
        $fullPath = $targetDir . '/' . $name;
        if (file_put_contents($fullPath, $raw) === false) {
            return null;
        }

        return 'storage/uploads/avatars/' . $name;
    }

    private function isAllowedAvatarUrl(string $url): bool
    {
        if (!preg_match('#^https?://#i', $url)) {
            return false;
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return false;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host === '' || in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return false;
        }

        $port = isset($parts['port']) ? (int) $parts['port'] : null;
        if ($port !== null && !in_array($port, [80, 443], true)) {
            return false;
        }

        $ips = gethostbynamel($host);
        if (!is_array($ips) || $ips === []) {
            return false;
        }

        foreach ($ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return false;
            }
        }

        return true;
    }
}