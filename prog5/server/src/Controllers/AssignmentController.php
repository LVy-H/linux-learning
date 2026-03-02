<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Core\View;
use App\Models\Assignment;
use App\Models\Submission;

final class AssignmentController
{
    public function index(): Response
    {
        return Response::html(View::render('assignments', [
            'assignments' => (new Assignment())->all(),
            'currentUser' => Auth::user(),
            'error' => '',
        ]));
    }

    public function create(): Response
    {
        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $file = $_FILES['file'] ?? null;

        if ($title === '' || !is_array($file) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return Response::html(View::render('assignments', [
                'assignments' => (new Assignment())->all(),
                'currentUser' => Auth::user(),
                'error' => 'Title and assignment file are required.',
            ]), 422);
        }

        $path = $this->storeFile($file, 'assignments');
        if ($path === null) {
            return Response::html(View::render('assignments', [
                'assignments' => (new Assignment())->all(),
                'currentUser' => Auth::user(),
                'error' => 'Could not upload assignment file.',
            ]), 422);
        }

        (new Assignment())->create((int) Auth::id(), $title, $description, $path);
        return Response::redirect('/assignments');
    }

    public function download(string $id): Response
    {
        $assignment = (new Assignment())->findById((int) $id);
        if (!$assignment) {
            return Response::html('Not found', 404);
        }

        $path = realpath(__DIR__ . '/../../' . ltrim($assignment['file_path'], '/'));
        if ($path === false || !is_file($path)) {
            return Response::html('File not found', 404);
        }

        $mime = mime_content_type($path) ?: 'application/octet-stream';
        return new Response(
            file_get_contents($path) ?: '',
            200,
            [
                'Content-Type' => $mime,
                'Content-Disposition' => 'attachment; filename="' . basename($path) . '"',
            ]
        );
    }

    public function submit(string $id): Response
    {
        $assignment = (new Assignment())->findById((int) $id);
        if (!$assignment) {
            return Response::html('Assignment not found', 404);
        }

        $file = $_FILES['submission_file'] ?? null;
        if (!is_array($file) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return Response::html('Submission file is required', 422);
        }

        $path = $this->storeFile($file, 'submissions');
        if ($path === null) {
            return Response::html('Could not upload submission', 422);
        }

        (new Submission())->create((int) $id, (int) Auth::id(), $path);
        return Response::redirect('/assignments');
    }

    public function submissions(): Response
    {
        return Response::html(View::render('submissions', [
            'submissions' => (new Submission())->allForTeacher(),
            'currentUser' => Auth::user(),
        ]));
    }

    public function downloadSubmission(string $id): Response
    {
        $submission = (new Submission())->findById((int) $id);
        if (!$submission) {
            return Response::html('Not found', 404);
        }

        $path = realpath(__DIR__ . '/../../' . ltrim($submission['file_path'], '/'));
        if ($path === false || !is_file($path)) {
            return Response::html('File not found', 404);
        }

        $mime = mime_content_type($path) ?: 'application/octet-stream';
        return new Response(
            file_get_contents($path) ?: '',
            200,
            [
                'Content-Type' => $mime,
                'Content-Disposition' => 'attachment; filename="' . basename($path) . '"',
            ]
        );
    }

    private function storeFile(array $file, string $folder): ?string
    {
        $config = require __DIR__ . '/../../config/config.php';
        $base = rtrim($config['upload_dir'], '/');
        $targetDir = $base . '/' . $folder;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $original = (string) ($file['name'] ?? 'upload.bin');
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $safeExt = $ext !== '' ? $ext : 'bin';
        $name = uniqid($folder . '_', true) . '.' . $safeExt;
        $fullPath = $targetDir . '/' . $name;

        if (!move_uploaded_file((string) $file['tmp_name'], $fullPath)) {
            return null;
        }

        return 'storage/uploads/' . $folder . '/' . $name;
    }
}
