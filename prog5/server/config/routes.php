<?php

declare(strict_types= 1);

use App\Core\Router;

return function (Router $router) {
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
    $router->get("/user/{id}", function(string $id) {
        echo "You are viewing the profile for User ID: " . htmlspecialchars($id);
    });
};