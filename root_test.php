<?php
// Simple test file to check if the server is accessible from the root directory
echo "Root directory is accessible at " . $_SERVER['REQUEST_URI'];
echo "<br>Full URL: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
echo "<br>Document Root: " . $_SERVER['DOCUMENT_ROOT'];
echo "<br>Script Filename: " . $_SERVER['SCRIPT_FILENAME'];
echo "<br><br>This file is in the root directory of the Laravel application, not in the public directory.";
?>
