<?php
declare(strict_types= 1);

define('ENV', 'development'); 
if (ENV === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}
require_once __DIR__ . '/../autoload.php';

use App\Core\Router;

$router = new Router();

$router->get('/', function() {
    echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Home</title>
</head>
<body>
    <h1>Welcome to the Home Page</h1>
</body>
</html>";
});

$router->get('/user/{id}', function (string $id) {
    echo "You are viewing the profile for User ID: " . htmlspecialchars($id);
});

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
