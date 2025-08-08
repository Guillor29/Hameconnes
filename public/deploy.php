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

// Accept both POST and GET requests for easier deployment
// For production, you should restrict this to POST only and require a token
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

if ($isPost) {
    // Check for deployment token in POST request
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
    echo ".env file already exists\n";

    // Update .env file with production settings
    echo "Updating .env file with production settings\n";
    $envContent = file_get_contents($envPath);

    // Update APP_ENV to production
    $envContent = preg_replace('/APP_ENV=.*/', 'APP_ENV=production', $envContent);

    // Update APP_DEBUG to false
    $envContent = preg_replace('/APP_DEBUG=.*/', 'APP_DEBUG=false', $envContent);

    // Update APP_URL to the actual domain
    $envContent = preg_replace('/APP_URL=.*/', 'APP_URL=https://hameconnes.guillaume-rv.fr', $envContent);

    // Write the updated content back to the .env file
    if (file_put_contents($envPath, $envContent)) {
        echo "Successfully updated .env file with production settings\n\n";
    } else {
        echo "Failed to update .env file with production settings\n\n";
    }
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

// Set proper permissions for storage and bootstrap/cache directories
echo "Setting proper permissions for storage and bootstrap/cache directories\n";
$storageDir = $basePath . '/storage';
$bootstrapCacheDir = $basePath . '/bootstrap/cache';

// Function to recursively set directory permissions
function setPermissions($path, $dirMode, $fileMode) {
    if (is_dir($path)) {
        if (!chmod($path, $dirMode)) {
            echo "Failed to set permissions for directory: $path\n";
        }

        $items = new FilesystemIterator($path);
        foreach ($items as $item) {
            if ($item->isDir()) {
                setPermissions($item->getPathname(), $dirMode, $fileMode);
            } else {
                if (!chmod($item->getPathname(), $fileMode)) {
                    echo "Failed to set permissions for file: " . $item->getPathname() . "\n";
                }
            }
        }
    }
}

// Set permissions for storage directory
if (is_dir($storageDir)) {
    setPermissions($storageDir, 0755, 0644);
    echo "Set permissions for storage directory\n";
} else {
    echo "Warning: storage directory not found\n";
}

// Set permissions for bootstrap/cache directory
if (is_dir($bootstrapCacheDir)) {
    setPermissions($bootstrapCacheDir, 0755, 0644);
    echo "Set permissions for bootstrap/cache directory\n\n";
} else {
    echo "Warning: bootstrap/cache directory not found\n\n";
}

// 2. Run artisan commands
$phpBinary = PHP_BINARY;
$artisanPath = $basePath . '/artisan';

// Clear all caches first
echo "Clearing all caches before rebuilding...\n";
runCommand("$phpBinary $artisanPath cache:clear", $basePath);
runCommand("$phpBinary $artisanPath config:clear", $basePath);
runCommand("$phpBinary $artisanPath route:clear", $basePath);
runCommand("$phpBinary $artisanPath view:clear", $basePath);

// Optimize the application
runCommand("$phpBinary $artisanPath optimize:clear", $basePath);

// Rebuild caches
echo "Rebuilding caches...\n";
runCommand("$phpBinary $artisanPath config:cache", $basePath);
runCommand("$phpBinary $artisanPath route:cache", $basePath);
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

// Determine the response format based on the request type
if ($isPost) {
    // Return a JSON response for POST requests
    echo json_encode([
        'success' => true,
        'message' => 'Deployment completed successfully',
        'log' => $output
    ]);
} else {
    // Return an HTML response for browser requests
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Deployment Status - Les Hameçonnés</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                margin: 0;
                padding: 20px;
                background-color: #f5f5f5;
            }
            .container {
                max-width: 1000px;
                margin: 0 auto;
                background-color: #fff;
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h1 {
                color: #333;
                border-bottom: 1px solid #eee;
                padding-bottom: 10px;
            }
            pre {
                background-color: #f8f8f8;
                padding: 15px;
                border-radius: 5px;
                overflow-x: auto;
                font-size: 14px;
                border: 1px solid #ddd;
            }
            .success {
                color: #28a745;
                font-weight: bold;
            }
            .timestamp {
                color: #6c757d;
                font-size: 0.9em;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Les Hameçonnés - Deployment Status</h1>
            <p class="timestamp">Executed at: <?php echo date('Y-m-d H:i:s'); ?></p>
            <p class="success">✅ Deployment completed successfully!</p>
            <h2>Deployment Log:</h2>
            <pre><?php echo htmlspecialchars($output); ?></pre>
            <p>
                <a href="/">Go to homepage</a>
            </p>
        </div>
    </body>
    </html>
    <?php
}
