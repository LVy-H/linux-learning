<?php

declare(strict_types= 1);

use PDO;

final class User {
    public function __construct(
        private int $id,
        private string $name,
        private string $email
    ) {}
}