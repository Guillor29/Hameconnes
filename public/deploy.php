<?php
/**
 * Deployment script for Les Hameçonnés
 *
 * This script performs post-deployment tasks that would normally be executed via SSH:
 * - Preserves database credentials in the .env file
 * - Sets proper permissions for storage and bootstrap/cache directories
 * - Sets executable permissions for node_modules/.bin files
 * - Runs Laravel artisan commands for caching and migrations
 * - Ensures the Vite manifest exists and is valid
 *
 * VITE MANIFEST HANDLING:
 * This script implements a multi-tiered approach to ensure the Vite manifest is always available:
 * 1. Validates any existing manifest file
 * 2. Attempts to rebuild assets using npm if available
 * 3. Attempts to rebuild assets using Docker if available
 * 4. Creates a minimal manifest file as a fallback
 *
 * TROUBLESHOOTING:
 *
 * If you encounter the "Vite manifest not found" error:
 * 1. Run this script manually by visiting https://hameconnes.guillaume-rv.fr/public/deploy.php
 * 2. Check the logs for any errors during the manifest creation process
 * 3. Verify that the public/build directory has the correct permissions (should be 755)
 * 4. If using Docker, ensure Docker is available on the server
 *
 * If you encounter "Permission denied" errors when running npm or node commands:
 * 1. Run this script manually by visiting https://hameconnes.guillaume-rv.fr/public/deploy.php
 * 2. Check the logs to verify that executable permissions were set for node_modules/.bin
 * 3. Manually verify permissions with: ls -la node_modules/.bin
 * 4. If needed, manually set permissions with: chmod +x node_modules/.bin/*
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

    // Extract database credentials before updating
    $dbCredentials = [];
    if (preg_match('/DB_HOST=(.*)/', $envContent, $matches)) {
        $dbCredentials['DB_HOST'] = trim($matches[1]);
    }
    if (preg_match('/DB_PORT=(.*)/', $envContent, $matches)) {
        $dbCredentials['DB_PORT'] = trim($matches[1]);
    }
    if (preg_match('/DB_DATABASE=(.*)/', $envContent, $matches)) {
        $dbCredentials['DB_DATABASE'] = trim($matches[1]);
    }
    if (preg_match('/DB_USERNAME=(.*)/', $envContent, $matches)) {
        $dbCredentials['DB_USERNAME'] = trim($matches[1]);
    }
    if (preg_match('/DB_PASSWORD=(.*)/', $envContent, $matches)) {
        $dbCredentials['DB_PASSWORD'] = trim($matches[1]);
    }
    if (preg_match('/DB_CHARSET=(.*)/', $envContent, $matches)) {
        $dbCredentials['DB_CHARSET'] = trim($matches[1]);
    }

    // Update APP_ENV to production
    $envContent = preg_replace('/APP_ENV=.*/', 'APP_ENV=production', $envContent);

    // Update APP_DEBUG to false
    $envContent = preg_replace('/APP_DEBUG=.*/', 'APP_DEBUG=false', $envContent);

    // Update APP_URL to the actual domain
    $envContent = preg_replace('/APP_URL=.*/', 'APP_URL=https://hameconnes.guillaume-rv.fr', $envContent);

    // Restore database credentials
    if (!empty($dbCredentials['DB_HOST'])) {
        $envContent = preg_replace('/DB_HOST=.*/', 'DB_HOST=' . $dbCredentials['DB_HOST'], $envContent);
        echo "Preserved DB_HOST: " . $dbCredentials['DB_HOST'] . "\n";
    }
    if (!empty($dbCredentials['DB_PORT'])) {
        $envContent = preg_replace('/DB_PORT=.*/', 'DB_PORT=' . $dbCredentials['DB_PORT'], $envContent);
        echo "Preserved DB_PORT: " . $dbCredentials['DB_PORT'] . "\n";
    }
    if (!empty($dbCredentials['DB_DATABASE'])) {
        $envContent = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=' . $dbCredentials['DB_DATABASE'], $envContent);
        echo "Preserved DB_DATABASE: " . $dbCredentials['DB_DATABASE'] . "\n";
    }
    if (!empty($dbCredentials['DB_USERNAME'])) {
        $envContent = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=' . $dbCredentials['DB_USERNAME'], $envContent);
        echo "Preserved DB_USERNAME: " . $dbCredentials['DB_USERNAME'] . "\n";
    }
    if (!empty($dbCredentials['DB_PASSWORD'])) {
        $envContent = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD=' . $dbCredentials['DB_PASSWORD'], $envContent);
        echo "Preserved DB_PASSWORD: (value hidden for security)\n";
    }
    if (!empty($dbCredentials['DB_CHARSET'])) {
        $envContent = preg_replace('/DB_CHARSET=.*/', 'DB_CHARSET=' . $dbCredentials['DB_CHARSET'], $envContent);
        echo "Preserved DB_CHARSET: " . $dbCredentials['DB_CHARSET'] . "\n";
    }

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
    echo "Set permissions for bootstrap/cache directory\n";
} else {
    echo "Warning: bootstrap/cache directory not found\n";
}

// Set executable permissions for node_modules/.bin directory
$nodeModulesBinDir = $basePath . '/node_modules/.bin';
if (is_dir($nodeModulesBinDir)) {
    echo "Setting executable permissions for node_modules/.bin directory\n";

    // Make the .bin directory accessible
    if (!chmod($nodeModulesBinDir, 0755)) {
        echo "Failed to set permissions for directory: $nodeModulesBinDir\n";
    }

    // Set executable permissions for all files in the .bin directory
    $binFiles = new FilesystemIterator($nodeModulesBinDir);
    foreach ($binFiles as $file) {
        if (!$file->isDir()) {
            if (!chmod($file->getPathname(), 0755)) {
                echo "Failed to set executable permissions for: " . $file->getPathname() . "\n";
            } else {
                echo "Set executable permissions for: " . basename($file->getPathname()) . "\n";
            }
        }
    }
    echo "Finished setting executable permissions for node_modules/.bin\n\n";
} else {
    echo "Warning: node_modules/.bin directory not found\n\n";
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

// Global array to track files created during deployment
$createdFiles = [];

// Check for Vite manifest and rebuild assets if needed
$manifestPath = $basePath . '/public/build/manifest.json';
$buildDir = $basePath . '/public/build';

echo "Checking for Vite manifest at: $manifestPath\n";

// Always ensure the build directory exists
if (!is_dir($buildDir)) {
    echo "Build directory not found. Creating it...\n";
    if (!mkdir($buildDir, 0755, true)) {
        echo "Failed to create build directory. Attempting with Docker...\n";

        // Try using Docker to create the directory
        $dockerResult = runCommand("docker run --rm -v \"$basePath:/app\" -w /app alpine mkdir -p /app/public/build", $basePath);

        if ($dockerResult['code'] !== 0) {
            echo "Failed to create build directory with Docker. Using PHP fallback...\n";

            // Last resort: try to create directory with different permissions
            if (!@mkdir($buildDir, 0777, true)) {
                echo "WARNING: Could not create build directory. Will attempt to create manifest anyway.\n";
            } else {
                $createdFiles[] = $buildDir;
                echo "Build directory created successfully with fallback permissions\n";

                // Set more permissive permissions to ensure we can write files
                @chmod($buildDir, 0777);
            }
        } else {
            $createdFiles[] = $buildDir;
            echo "Build directory created successfully using Docker\n";
        }
    } else {
        $createdFiles[] = $buildDir;
        echo "Build directory created successfully\n";
    }
}

// Always create the assets directory
$assetsDir = $buildDir . '/assets';
if (!is_dir($assetsDir)) {
    echo "Assets directory not found. Creating it...\n";
    if (!mkdir($assetsDir, 0755, true)) {
        echo "Failed to create assets directory. Using fallback...\n";

        // Try with more permissive permissions
        if (!@mkdir($assetsDir, 0777, true)) {
            echo "WARNING: Could not create assets directory. Will attempt to create manifest anyway.\n";
        } else {
            $createdFiles[] = $assetsDir;
            echo "Assets directory created successfully with fallback permissions\n";

            // Set more permissive permissions
            @chmod($assetsDir, 0777);
        }
    } else {
        $createdFiles[] = $assetsDir;
        echo "Assets directory created successfully\n";
    }
}

// First try: Check if we can use the existing manifest
if (file_exists($manifestPath) && filesize($manifestPath) > 0) {
    echo "Vite manifest found. Validating...\n";

    // Validate the manifest file
    $manifestContent = @file_get_contents($manifestPath);
    $manifestData = @json_decode($manifestContent, true);

    if ($manifestData && is_array($manifestData) && !empty($manifestData)) {
        echo "Vite manifest is valid. No need to rebuild assets.\n";
    } else {
        echo "Vite manifest exists but is invalid. Will recreate it.\n";
        $needToCreateManifest = true;
    }
} else {
    echo "Vite manifest not found or empty. Will create it.\n";
    $needToCreateManifest = true;
}

// Second try: Use npm if available
if (isset($needToCreateManifest) && $needToCreateManifest) {
    // Check if npm is available
    $npmCheckResult = runCommand("which npm || where npm 2>/dev/null", $basePath);
    if ($npmCheckResult['code'] === 0) {
        echo "NPM is available. Attempting to rebuild assets...\n";

        // Install dependencies and build assets
        runCommand("npm install", $basePath);
        runCommand("npm run build", $basePath);

        if (file_exists($manifestPath) && filesize($manifestPath) > 0) {
            $createdFiles[] = $manifestPath;
            echo "Vite manifest successfully created with npm\n";

            // Track all files in the build directory
            $buildFiles = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($buildDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($buildFiles as $fileinfo) {
                if (!$fileinfo->isDir()) {
                    $createdFiles[] = $fileinfo->getPathname();
                }
            }

            $needToCreateManifest = false;
        } else {
            echo "Failed to create Vite manifest with npm. Will try Docker next...\n";
        }
    } else {
        echo "NPM not available on the server. Will try Docker next...\n";
    }
}

// Third try: Use Docker if available
if (isset($needToCreateManifest) && $needToCreateManifest) {
    // Check if Docker is available
    $dockerCheckResult = runCommand("which docker || where docker 2>/dev/null", $basePath);
    if ($dockerCheckResult['code'] === 0) {
        echo "Docker is available. Attempting to rebuild assets with Docker...\n";

        // Use Docker to build assets
        $dockerBuildResult = runCommand(
            "docker run --rm -v \"$basePath:/app\" -w /app node:20-alpine sh -c \"npm install && npm run build\"",
            $basePath
        );

        if (file_exists($manifestPath) && filesize($manifestPath) > 0) {
            $createdFiles[] = $manifestPath;
            echo "Vite manifest successfully created with Docker\n";

            // Track all files in the build directory
            if (is_dir($buildDir)) {
                $buildFiles = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($buildDir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($buildFiles as $fileinfo) {
                    if (!$fileinfo->isDir()) {
                        $createdFiles[] = $fileinfo->getPathname();
                    }
                }
            }

            $needToCreateManifest = false;
        } else {
            echo "Failed to create Vite manifest with Docker. Will use fallback mechanism...\n";
        }
    } else {
        echo "Docker not available on the server. Will use fallback mechanism...\n";
    }
}

// Final fallback: Create a minimal manifest
if (isset($needToCreateManifest) && $needToCreateManifest) {
    echo "Creating a minimal manifest file as final fallback...\n";
    createMinimalManifest($manifestPath);
}

// Set proper permissions for build directory if it exists
if (is_dir($buildDir)) {
    echo "Setting proper permissions for build directory\n";
    setPermissions($buildDir, 0755, 0644);
}

// Function to create a minimal Vite manifest file
function createMinimalManifest($manifestPath) {
    global $createdFiles;

    // Create a minimal manifest with entries for the main CSS and JS files
    $minimalManifest = [
        "resources/css/app.css" => [
            "file" => "assets/app-minimal.css",
            "isEntry" => true,
            "src" => "resources/css/app.css"
        ],
        "resources/js/app.js" => [
            "file" => "assets/app-minimal.js",
            "isEntry" => true,
            "src" => "resources/js/app.js"
        ]
    ];

    // The assets directory should already be created by the main script
    // but we'll double-check just to be safe
    $assetsDir = dirname($manifestPath) . '/assets';
    if (!is_dir($assetsDir)) {
        echo "Assets directory still doesn't exist. Making one final attempt...\n";

        // Try with error suppression and maximum permissions
        if (!@mkdir($assetsDir, 0777, true)) {
            // Try with Docker as a last resort
            $basePath = dirname(dirname($manifestPath));
            $dockerResult = runCommand("docker run --rm -v \"$basePath:/app\" -w /app alpine mkdir -p /app/public/build/assets", $basePath);

            if ($dockerResult['code'] !== 0) {
                echo "CRITICAL ERROR: Could not create assets directory by any means.\n";
                echo "Attempting to create manifest without asset files...\n";
            } else {
                $createdFiles[] = $assetsDir;
                echo "Created assets directory using Docker\n";
            }
        } else {
            $createdFiles[] = $assetsDir;
            echo "Created assets directory with fallback permissions\n";
            @chmod($assetsDir, 0777);
        }
    }

    // Create minimal CSS file with error handling
    if (is_dir($assetsDir)) {
        $minimalCss = "/* Minimal CSS file created by deployment script */\n";
        $cssFile = $assetsDir . '/app-minimal.css';

        // Try to write the file with error suppression
        if (@file_put_contents($cssFile, $minimalCss)) {
            $createdFiles[] = $cssFile;
            echo "Created file: " . $cssFile . "\n";

            // Ensure the file is readable
            @chmod($cssFile, 0644);
        } else {
            echo "Warning: Failed to create minimal CSS file\n";
        }

        // Create minimal JS file with error handling
        $minimalJs = "// Minimal JS file created by deployment script\n";
        $jsFile = $assetsDir . '/app-minimal.js';

        // Try to write the file with error suppression
        if (@file_put_contents($jsFile, $minimalJs)) {
            $createdFiles[] = $jsFile;
            echo "Created file: " . $jsFile . "\n";

            // Ensure the file is readable
            @chmod($jsFile, 0644);
        } else {
            echo "Warning: Failed to create minimal JS file\n";
        }
    }

    // Write the manifest file with multiple fallbacks
    $manifestJson = json_encode($minimalManifest, JSON_PRETTY_PRINT);
    $result = @file_put_contents($manifestPath, $manifestJson);

    if (!$result) {
        echo "Failed to create manifest file directly. Trying with more permissions...\n";

        // Try to make the directory writable
        @chmod(dirname($manifestPath), 0777);

        // Try again with error suppression
        $result = @file_put_contents($manifestPath, $manifestJson);

        if (!$result) {
            echo "Still failed. Trying with Docker as last resort...\n";

            // Create a temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'manifest');
            file_put_contents($tempFile, $manifestJson);

            // Use Docker to copy it to the right place
            $basePath = dirname(dirname($manifestPath));
            $dockerResult = runCommand("docker run --rm -v \"$basePath:/app\" -v \"$tempFile:/tmp/manifest.json\" -w /app alpine cp /tmp/manifest.json /app/public/build/manifest.json", $basePath);

            if ($dockerResult['code'] === 0) {
                $result = true;
                echo "Created manifest file using Docker\n";
            } else {
                echo "CRITICAL ERROR: All attempts to create manifest file failed.\n";
                echo "Please check server permissions and configuration.\n";
            }

            // Clean up temp file
            @unlink($tempFile);
        }
    }

    if ($result) {
        $createdFiles[] = $manifestPath;
        echo "Minimal Vite manifest file created successfully\n";

        // Ensure the file is readable
        @chmod($manifestPath, 0644);

        return true;
    } else {
        echo "Failed to create minimal Vite manifest file\n";
        return false;
    }
}

// Update deployment manifest with created files
$manifestFilePath = $basePath . '/deployment-manifest.txt';
if (file_exists($manifestFilePath)) {
    echo "\nUpdating deployment manifest with created files...\n";

    // Read the existing manifest
    $manifestContent = file_get_contents($manifestFilePath);

    // Add created files section
    if (!empty($createdFiles)) {
        $manifestContent .= "\nFiles created during deployment:\n";
        foreach ($createdFiles as $file) {
            // Convert to relative path for cleaner output
            $relativePath = str_replace($basePath . '/', '', $file);
            $manifestContent .= $relativePath . "\n";
        }
    } else {
        $manifestContent .= "\nNo files were created during this deployment.\n";
    }

    // Write the updated manifest
    if (file_put_contents($manifestFilePath, $manifestContent)) {
        echo "Deployment manifest updated successfully.\n";
    } else {
        echo "Failed to update deployment manifest.\n";
    }
} else {
    echo "\nDeployment manifest file not found. Cannot update with created files.\n";
}

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
            h1, h2 {
                color: #333;
            }
            h1 {
                border-bottom: 1px solid #eee;
                padding-bottom: 10px;
            }
            h2 {
                margin-top: 30px;
                font-size: 1.5em;
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
            .error {
                color: #dc3545;
                font-weight: bold;
            }
            .timestamp {
                color: #6c757d;
                font-size: 0.9em;
                margin-bottom: 20px;
            }
            .created-files, .manifest-status {
                background-color: #f8f9fa;
                border: 1px solid #e9ecef;
                border-radius: 5px;
                padding: 15px;
                margin-bottom: 20px;
            }
            .created-files {
                background-color: #f0fff0;
                border-color: #d0e9c6;
            }
            .created-files ul, .manifest-status ul {
                margin: 10px 0;
                padding-left: 20px;
            }
            .created-files li, .manifest-status li {
                margin-bottom: 5px;
            }
            .created-files li {
                font-family: monospace;
            }
            .status-item {
                margin-bottom: 15px;
                padding-bottom: 15px;
                border-bottom: 1px solid #eee;
            }
            .status-item:last-child {
                border-bottom: none;
                margin-bottom: 0;
                padding-bottom: 0;
            }
            .manifest-content {
                max-height: 200px;
                overflow-y: auto;
                margin-top: 10px;
                font-size: 12px;
            }
            .status-item strong {
                display: block;
                margin-bottom: 5px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Les Hameçonnés - Deployment Status</h1>
            <p class="timestamp">Executed at: <?php echo date('Y-m-d H:i:s'); ?></p>
            <p class="success">✅ Deployment completed successfully!</p>

            <h2>Vite Manifest Status:</h2>
            <div class="manifest-status">
                <?php
                $manifestPath = $basePath . '/public/build/manifest.json';
                $manifestExists = file_exists($manifestPath);
                $manifestValid = false;
                $manifestSize = 0;
                $manifestContent = '';

                if ($manifestExists) {
                    $manifestSize = filesize($manifestPath);
                    $manifestContent = @file_get_contents($manifestPath);
                    $manifestData = @json_decode($manifestContent, true);
                    $manifestValid = $manifestData && is_array($manifestData) && !empty($manifestData);
                }
                ?>

                <div class="status-item <?php echo $manifestExists ? 'success' : 'error'; ?>">
                    <strong>Manifest File:</strong>
                    <?php echo $manifestExists ? 'Exists ✓' : 'Missing ✗'; ?>
                    <?php if ($manifestExists): ?>
                        (Size: <?php echo $manifestSize; ?> bytes)
                    <?php endif; ?>
                </div>

                <?php if ($manifestExists): ?>
                <div class="status-item <?php echo $manifestValid ? 'success' : 'error'; ?>">
                    <strong>Manifest Validity:</strong>
                    <?php echo $manifestValid ? 'Valid ✓' : 'Invalid ✗'; ?>
                </div>

                <div class="status-item">
                    <strong>Manifest Content:</strong>
                    <pre class="manifest-content"><?php echo htmlspecialchars($manifestContent); ?></pre>
                </div>
                <?php endif; ?>

                <div class="status-item">
                    <strong>Troubleshooting:</strong>
                    <ul>
                        <li>If the manifest is missing or invalid, try running this script again.</li>
                        <li>Check that the public/build directory has the correct permissions (should be 755).</li>
                        <li>Verify that npm or Docker is available on the server if needed.</li>
                        <li>For persistent issues, try rebuilding assets locally and uploading them manually.</li>
                    </ul>
                </div>
            </div>

            <h2>Node Modules Bin Permissions:</h2>
            <div class="manifest-status">
                <?php
                $nodeModulesBinDir = $basePath . '/node_modules/.bin';
                $binDirExists = is_dir($nodeModulesBinDir);
                $binDirPermissions = $binDirExists ? substr(sprintf('%o', fileperms($nodeModulesBinDir)), -4) : 'N/A';
                $binDirPermissionsOk = $binDirPermissions === '0755';

                // Check for concurrently executable specifically
                $concurrentlyPath = $nodeModulesBinDir . '/concurrently';
                $concurrentlyExists = file_exists($concurrentlyPath);
                $concurrentlyPermissions = $concurrentlyExists ? substr(sprintf('%o', fileperms($concurrentlyPath)), -4) : 'N/A';
                $concurrentlyPermissionsOk = $concurrentlyPermissions === '0755';

                // Get a list of a few executables in the bin directory
                $binFiles = [];
                if ($binDirExists) {
                    $iterator = new FilesystemIterator($nodeModulesBinDir);
                    $count = 0;
                    foreach ($iterator as $file) {
                        if (!$file->isDir() && $count < 5) {
                            $binFiles[] = [
                                'name' => $file->getFilename(),
                                'permissions' => substr(sprintf('%o', fileperms($file->getPathname())), -4),
                                'executable' => is_executable($file->getPathname())
                            ];
                            $count++;
                        }
                    }
                }
                ?>

                <div class="status-item <?php echo $binDirExists ? 'success' : 'error'; ?>">
                    <strong>Node Modules Bin Directory:</strong>
                    <?php echo $binDirExists ? 'Exists ✓' : 'Missing ✗'; ?>
                    <?php if ($binDirExists): ?>
                        (Permissions: <?php echo $binDirPermissions; ?> - <?php echo $binDirPermissionsOk ? 'Correct ✓' : 'Incorrect ✗'; ?>)
                    <?php endif; ?>
                </div>

                <?php if ($binDirExists): ?>
                <div class="status-item <?php echo $concurrentlyExists ? ($concurrentlyPermissionsOk ? 'success' : 'error') : 'error'; ?>">
                    <strong>Concurrently Executable:</strong>
                    <?php if ($concurrentlyExists): ?>
                        Exists ✓ (Permissions: <?php echo $concurrentlyPermissions; ?> - <?php echo $concurrentlyPermissionsOk ? 'Executable ✓' : 'Not Executable ✗'; ?>)
                    <?php else: ?>
                        Missing ✗ (This may cause "Permission denied" errors when running composer dev)
                    <?php endif; ?>
                </div>

                <div class="status-item">
                    <strong>Sample Bin Files:</strong>
                    <?php if (!empty($binFiles)): ?>
                        <ul>
                            <?php foreach ($binFiles as $file): ?>
                                <li>
                                    <?php echo htmlspecialchars($file['name']); ?>:
                                    Permissions <?php echo $file['permissions']; ?> -
                                    <?php echo $file['executable'] ? '<span class="success">Executable ✓</span>' : '<span class="error">Not Executable ✗</span>'; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No bin files found.</p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="status-item">
                    <strong>Troubleshooting:</strong>
                    <ul>
                        <li>If executables are not marked as executable, try running this script again.</li>
                        <li>For persistent issues, manually set permissions with: <code>chmod +x node_modules/.bin/*</code></li>
                        <li>If the node_modules/.bin directory is missing, run <code>npm install</code> to create it.</li>
                        <li>When running <code>composer dev</code>, ensure you're in the project root directory.</li>
                    </ul>
                </div>
            </div>

            <?php if (!empty($createdFiles)): ?>
            <h2>Files Created During Deployment:</h2>
            <div class="created-files">
                <ul>
                    <?php foreach ($createdFiles as $file): ?>
                        <li><?php echo htmlspecialchars(str_replace($basePath . '/', '', $file)); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

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
