<?php

// Get database credentials from .env file
$host = '109.234.165.93';
$port = '3306';
$database = 'bfps0361_Hameconnes';
$username = 'bfps0361_Hameconnes';
$password = '#!Perc!29260!#';

// Try to connect using PDO
try {
    echo "Attempting to connect to database...\n";
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Connection successful!\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
