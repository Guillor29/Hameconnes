<?php
/**
 * Deployment script for Les Hameçonnés
 *
 * This script performs post-deployment tasks that would normally be executed via SSH:
 * - Renames .env.production to .env
 * - Runs Laravel artisan commands for caching and migrations
 *
 * SECURITY NOTICE:
 * This script should be protected with a deployment token to prevent unauthorized access.
 * The token should be stored as a secret in GitHub and passed as a parameter to this script.
 */

// Exit if not a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check for deployment token
$input = json_decode(file_get_contents('php://input'), true);
$providedToken = isset($input['token']) ? $input['token'] : '';
$expectedToken = isset($_ENV['DEPLOYMENT_TOKEN']) ? $_ENV['DEPLOYMENT_TOKEN'] : '';

// If no token is set in the environment, generate a warning
if (empty($expectedToken)) {
    error_log('WARNING: DEPLOYMENT_TOKEN is not set in the environment. This is a security risk.');
}

// Verify the token (skip verification if no token is set, but log a warning)
if (!empty($expectedToken) && $providedToken !== $expectedToken) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Start output buffering to capture all output
ob_start();

// Define the base path (assuming this script is in the public directory)
$basePath = dirname(__DIR__);

// Function to run a command and log the output
function runCommand($command, $workingDir = null) {
    echo "Running: $command\n";

    $descriptorSpec = [
        0 => ["pipe", "r"],  // stdin
        1 => ["pipe", "w"],  // stdout
        2 => ["pipe", "w"]   // stderr
    ];

    $process = proc_open($command, $descriptorSpec, $pipes, $workingDir);

    if (is_resource($process)) {
        // Close stdin
        fclose($pipes[0]);

        // Read stdout
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        // Read stderr
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        // Close process
        $returnCode = proc_close($process);

        echo "Output:\n$output\n";

        if ($error) {
            echo "Error:\n$error\n";
        }

        echo "Return code: $returnCode\n\n";

        return [
            'output' => $output,
            'error' => $error,
            'code' => $returnCode
        ];
    } else {
        echo "Failed to execute command\n\n";
        return [
            'output' => '',
            'error' => 'Failed to execute command',
            'code' => -1
        ];
    }
}

// Log the start of deployment
echo "Starting deployment process at " . date('Y-m-d H:i:s') . "\n\n";

// Check for .env file
$envPath = $basePath . '/.env';
$envProductionPath = $basePath . '/.env.production';

if (file_exists($envPath)) {
    echo ".env file already exists\n\n";
} elseif (file_exists($envProductionPath)) {
    echo "Renaming .env.production to .env\n";
    if (rename($envProductionPath, $envPath)) {
        echo "Successfully renamed .env.production to .env\n\n";
    } else {
        echo "Failed to rename .env.production to .env\n\n";
    }
} else {
    echo "Warning: Neither .env nor .env.production file found\n\n";
}

// 2. Run artisan commands
$phpBinary = PHP_BINARY;
$artisanPath = $basePath . '/artisan';

// Config cache
runCommand("$phpBinary $artisanPath config:cache", $basePath);

// Route cache
runCommand("$phpBinary $artisanPath route:cache", $basePath);

// View cache
runCommand("$phpBinary $artisanPath view:cache", $basePath);

// Run migrations
runCommand("$phpBinary $artisanPath migrate --force", $basePath);

// Log the end of deployment
echo "\nDeployment process completed at " . date('Y-m-d H:i:s');

// Get the output buffer contents
$output = ob_get_clean();

// Create a log file with the output
$logFile = $basePath . '/storage/logs/deployment-' . date('Y-m-d-H-i-s') . '.log';
file_put_contents($logFile, $output);

// Return a JSON response
echo json_encode([
    'success' => true,
    'message' => 'Deployment completed successfully',
    'log' => $output
]);
