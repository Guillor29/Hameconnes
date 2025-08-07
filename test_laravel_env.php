<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

// Get the database configuration from Laravel
echo "Laravel Database Configuration:\n";
echo "DB_CONNECTION: " . env('DB_CONNECTION') . "\n";
echo "DB_HOST: " . env('DB_HOST') . "\n";
echo "DB_PORT: " . env('DB_PORT') . "\n";
echo "DB_DATABASE: " . env('DB_DATABASE') . "\n";
echo "DB_USERNAME: " . env('DB_USERNAME') . "\n";
echo "DB_PASSWORD: " . env('DB_PASSWORD') . "\n";
echo "DB_CHARSET: " . env('DB_CHARSET') . "\n";
