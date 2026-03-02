<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\Response;


final class MinigameController
{
    private string $gameFolder;

    public function __construct()
    {
        $this->gameFolder = __DIR__ . "/../../storage/uploads/gameupload";
        if (!is_dir($this->gameFolder)) {
            mkdir($this->gameFolder, 0755, true);
        }
    }


    public function getMinigames(): array
    {
        $files = scandir($this->gameFolder);
        $minigames = [];
        foreach ($files as $file) {
            if ($file != "." && $file != ".." && is_dir($this->gameFolder . '/' . $file) && !is_link($this->gameFolder . '/' . $file)) {
                $minigames[] = [
                    'name' => pathinfo($file, PATHINFO_FILENAME),
                    'path' => "/uploads/gameupload/$file",
                    'hint' => file_get_contents($this->gameFolder . '/' . pathinfo($file, PATHINFO_FILENAME) . '-hint.txt')
                ];
            }
        }
        return $minigames;
    }
    public function index(): Response
    {

        return Response::html(View::render('minigame', [
            'minigames' => $this->getMinigames()
        ]));
    }

    public function show(string $id): Response
    {
        if (!in_array($id, array_map(fn($mg) => $mg['name'], $this->getMinigames()))) {
            return Response::html('Minigame not found', 404);
        }
        return Response::html(View::render('minigame-detail', [
            'minigameId' => $id,
            'hint' => file_get_contents($this->gameFolder . '/' . $id . '-hint.txt'),
        ]));
    }

    public function create(): Response
    {
        $file = $_FILES['file'] ?? null;
        if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return Response::html('No file uploaded', 422);
        }

        $filename = basename($file['name']);
        $hint = $_POST['hint'] ?? '';
        $containerFolder = $this->gameFolder . '/' . uniqid('game_', false);
        if (!mkdir($containerFolder, 0755, true)) {
            return Response::html('Failed to create game folder', 500);
        }
        $targetPath = $containerFolder . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return Response::html('Failed to upload file', 500);
        }

        $hintFile = $containerFolder . '-hint.txt';
        file_put_contents($hintFile, $hint);

        return Response::redirect('/minigames');
    }

    public function solve(string $id, string $name): Response
    {
        $basePath = realpath($this->gameFolder);
        if ($basePath === false) {
            return Response::html('Game folder not found', 500);
        }

        $gamePath = realpath($this->gameFolder . '/' . $id);
        if ($gamePath === false) {
            return Response::html('Minigame not found', 404);
        }

        if (!str_starts_with($gamePath, $basePath . DIRECTORY_SEPARATOR) || !is_dir($gamePath) || is_link($gamePath)) {
            return Response::html('Minigame not found', 404);
        }

        if ($name === '' || str_contains($name, '/') || str_contains($name, '\\') || $name !== basename($name)) {
            return Response::html('Incorrect solution. Try again!');
        }

        foreach (scandir($gamePath) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $entryPath = $gamePath . '/' . $entry;
            if (!is_file($entryPath) || is_link($entryPath)) {
                continue;
            }

            if ($entry === $name) {
                return Response::html(View::render('minigame-solved', [
                    'minigameId' => $id,
                    'content' => file_get_contents($entryPath) ?: '',
                ]));
            }
        }

        return Response::html('Incorrect solution. Try again!');
    }
}