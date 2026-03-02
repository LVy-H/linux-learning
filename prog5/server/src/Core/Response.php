<?php

declare(strict_types= 1);

namespace App\Core;

final class Response {
    private mixed $data;
    private int $status;
    private array $headers;

    public function __construct(mixed $data, int $status = 200, array $headers = []) {
        $this->data = $data;
        $this->status = $status;
        $this->headers = $headers;
    }

    public static function json(mixed $data, int $status = 200, array $headers = []): self {
        return new self($data, $status, array_merge($headers, ['Content-Type' => 'application/json']));
    }

    public static function html(string $html, int $status = 200, array $headers = []): self {
        return new self($html, $status, array_merge($headers, ['Content-Type' => 'text/html']));
    }

    public static function redirect(string $location, int $status = 302): self {
        return new self('', $status, ['Location' => $location]);
    }

    public function send(): void {
        http_response_code($this->status);
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
        if (is_array($this->data) || is_object($this->data)) {
            header('Content-Type: application/json');
            echo json_encode($this->data);
        } else {
            echo $this->data;
        }
    }
}