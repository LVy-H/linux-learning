<?php
declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';
$env = $config['env'] ?? 'production';

if ($env === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}
require_once __DIR__ . '/../autoload.php';

use App\Core\Router;

$router = new Router();
$routes = require __DIR__ . '/../config/routes.php';
$routes($router);
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
