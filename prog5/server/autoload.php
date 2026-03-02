<?php 
declare(strict_types=1);

spl_autoload_register(function ($class) {
    $prefix = "App\\";
    $base_dir = __DIR__ .DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative_class = substr($class, strlen($prefix));

    $file = $base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});