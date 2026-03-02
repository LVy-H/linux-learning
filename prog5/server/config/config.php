<?php

declare(strict_types= 1);

return [
    'env' => getenv('APP_ENV') ?: 'development',
    'db' => [
        'host' => getenv('DB_HOST') ?: 'db',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'mydatabase',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: 'rootpassword',
        'charset' => 'utf8mb4',
    ],
    'upload_dir' => __DIR__ . '/../storage/uploads',
    'log_dir' => __DIR__ . '/../storage/logs',
];