<?php

declare(strict_types=1);

require_once __DIR__ . '/../autoload.php';

use App\Core\Database;

function db(): PDO
{
    return Database::connection();
}

$pdo = db();

function init_db(): void {
    Database::initSchema();
}

