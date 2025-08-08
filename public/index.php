<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

try {
    // Determine if the application is in maintenance mode...
    if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
        require $maintenance;
    }

// Register the Composer autoloader...
    require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
    /** @var Application $app */
    $app = require_once __DIR__.'/../bootstrap/app.php';
} catch (Throwable $e) {
    // Handle any exceptions that occur during the bootstrap process
    http_response_code(500);
    echo "An error occurred while bootstrapping the application: " . htmlspecialchars($e->getMessage());
    exit;
}

$app->handleRequest(Request::capture());
