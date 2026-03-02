<?php

declare(strict_types=1);

namespace App\Core;

final readonly class Request
{

    public function __construct(public string $method, public string $path, public array $query, public array $headers, public array $body)
    {
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $query = $_GET;
        $headers = getallheaders();
        $body = $_POST ?? [];
        return new self($method, $path, $query, $headers, $body);
    }
    
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function headers(string $key, mixed $default = null): mixed
    {
        return $this->headers[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }
}