<?php
/**
 * Redirect to the public directory
 * This file is placed in the root directory of the Laravel application
 * to handle cases where the web server is configured to serve from the root
 * instead of the public directory.
 */

// Check if the request is for a file that exists in the public directory
$requestUri = $_SERVER['REQUEST_URI'];
$publicPath = __DIR__ . '/public' . $requestUri;

// If the request is for a file that exists in the public directory, serve it directly
if (file_exists($publicPath) && !is_dir($publicPath)) {
    // Get the file extension
    $extension = pathinfo($publicPath, PATHINFO_EXTENSION);

    // Set the appropriate content type
    switch ($extension) {
        case 'css':
            header('Content-Type: text/css');
            break;
        case 'js':
            header('Content-Type: application/javascript');
            break;
        case 'jpg':
        case 'jpeg':
            header('Content-Type: image/jpeg');
            break;
        case 'png':
            header('Content-Type: image/png');
            break;
        case 'gif':
            header('Content-Type: image/gif');
            break;
        case 'svg':
            header('Content-Type: image/svg+xml');
            break;
        case 'json':
            header('Content-Type: application/json');
            break;
        // Add more content types as needed
    }

    // Output the file contents
    readfile($publicPath);
    exit;
}

// Otherwise, include the public/index.php file to bootstrap Laravel
require_once __DIR__ . '/public/index.php';
?>
