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

    public function addRoute(HTTPMethod $method, string $path, array | callable $handler, array $middlewares = []): void {
        $regex = preg_replace('/\{(\w+)\}/', '(?P<\1>[^/]+)', $path);
        $regex = '#^' . $regex . '$#';
        $this->routes[] = [
            'method' => $method,
            'regex' => $regex,
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    private function runMiddlewares(array $middlewares): bool {
        foreach ($middlewares as $middleware) {
            if (!is_callable($middleware)) {
                continue;
            }

            $result = $middleware();
            if ($result instanceof Response) {
                $result->send();
                return false;
            }

            if (is_string($result)) {
                echo $result;
                return false;
            }

            if ($result === false) {
                return false;
            }
        }

        return true;
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
                $args = array_values($params);

                if (!$this->runMiddlewares($route['middlewares'] ?? [])) {
                    return;
                }

                $handler = $route['handler'];
                if (is_callable($handler)) {
                    $result = $handler(...$args);
                    if ($result instanceof Response) {
                        $result->send();
                    } elseif (is_string($result)) {
                        echo $result;
                    }
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$controllerClass, $methodName] = $handler;
                    if (class_exists($controllerClass) && method_exists($controllerClass, $methodName)) {
                        $controller = new $controllerClass();
                        $result = $controller->$methodName(...$args);
                        if ($result instanceof Response) {
                            $result->send();
                        } elseif (is_string($result)) {
                            echo $result;
                        }
                        return;
                    }
                }
            }
        }
        http_response_code(404);
        echo "404 Not Found";
    }

    public function get(string $path, array | callable $handler, array $middlewares = []): void {
        $this->addRoute(HTTPMethod::GET, $path, $handler, $middlewares);
    }

    public function post(string $path, array | callable $handler, array $middlewares = []): void {
        $this->addRoute(HTTPMethod::POST, $path, $handler, $middlewares);
    }

    public function put(string $path, array | callable $handler, array $middlewares = []): void {
        $this->addRoute(HTTPMethod::PUT, $path, $handler, $middlewares);
    }

    public function delete(string $path, array | callable $handler, array $middlewares = []): void {
        $this->addRoute(HTTPMethod::DELETE, $path, $handler, $middlewares);
    }

    public function patch(string $path, array | callable $handler, array $middlewares = []): void {
        $this->addRoute(HTTPMethod::PATCH, $path, $handler, $middlewares);
    }
}