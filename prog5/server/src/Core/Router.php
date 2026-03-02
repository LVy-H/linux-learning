<?php
declare(strict_types= 1);

namespace App\Core;

enum HTTPMethod: string {
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case PATCH = 'PATCH';
}

class Router {
    private array $routes = [];

    public function addRoute(HTTPMethod $method, string $path, array | callable $handler): void {
        $regex = preg_replace('/\{(\w+)\}/', '(?P<\1>[^/]+)', $path);
        $regex = '#^' . $regex . '$#';
        $this->routes[] = [
            'method' => $method,
            'regex' => $regex,
            'handler' => $handler
        ];
    }

    public function dispatch(string $requestMethod, string $requestUri): void {
        $uri = parse_url($requestUri, PHP_URL_PATH) ?? '/';
        $method = HTTPMethod::tryFrom(strtoupper($requestMethod));
        if (!$method) {
            http_response_code(405);
            echo "Method Not Allowed";
            return;
        }
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['regex'], $uri, $matches)) {
                $params = array_filter($matches, fn($key) => is_string($key), ARRAY_FILTER_USE_KEY);   
                $handler = $route['handler'];
                if (is_callable($handler)) {
                    $handler(...$params);
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$controllerClass, $methodName] = $handler;
                    if (class_exists($controllerClass) && method_exists($controllerClass, $methodName)) {
                        $controller = new $controllerClass();
                        $controller->$methodName(...$params);
                        return;
                    }
                }
            }
        }
        http_response_code(404);
        echo "404 Not Found";
    }

    public function get(string $path, array | callable $handler): void {
        $this->addRoute(HTTPMethod::GET, $path, $handler);
    }

    public function post(string $path, array | callable $handler): void {
        $this->addRoute(HTTPMethod::POST, $path, $handler);
    }

    public function put(string $path, array | callable $handler): void {
        $this->addRoute(HTTPMethod::PUT, $path, $handler);
    }

    public function delete(string $path, array | callable $handler): void {
        $this->addRoute(HTTPMethod::DELETE, $path, $handler);
    }

    public function patch(string $path, array | callable $handler): void {
        $this->addRoute(HTTPMethod::PATCH, $path, $handler);
    }
}