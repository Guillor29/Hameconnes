# Deployment Guide for Les Hameçonnés

This document provides guidance on deploying the Les Hameçonnés application to production.

## Deployment Process

The application is deployed using GitHub Actions, which:

1. Builds the application (composer install, npm install, npm run build)
2. Creates a production .env file with secrets from GitHub
3. Uploads the files to the server via FTP
4. Executes the deployment script on the server

## Recent Updates to Deployment Process

### Database Credentials Preservation

The deployment script (`public/deploy.php`) has been updated to preserve database credentials when updating the .env file. This ensures that database connection details are not lost during deployment.

### Vite Assets Building

The deployment script now checks for the Vite manifest file and attempts to rebuild assets if it's missing. This addresses the "Vite manifest not found" error that can occur if assets are not properly built or deployed.

## Troubleshooting

### Database Connection Issues

If you encounter database connection issues after deployment:

1. Check that the database credentials in the .env file are correct
2. Verify that the database server is accessible from the web server
3. Ensure that the database user has the necessary permissions

### Vite Manifest Not Found

If you see the error "Vite manifest not found":

1. Run the deployment script (`public/deploy.php`) in your browser to attempt automatic rebuilding of assets
2. If automatic rebuilding fails, you may need to build assets locally and upload them manually:
   ```
   npm install
   npm run build
   ```
   Then upload the `public/build` directory to the server

### Manual Deployment Steps

If you need to deploy manually:

1. Upload all files to the server
2. Ensure the .env file has the correct production settings
3. Set proper permissions:
   - `storage` directory: 755 for directories, 644 for files
   - `bootstrap/cache` directory: 755 for directories, 644 for files
   - `public/build` directory: 755 for directories, 644 for files
4. Run the following commands:
   ```
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan migrate --force
   ```

## GitHub Workflow Configuration

The GitHub workflow is configured in `.github/workflows/deploy.yml`. It uses secrets for sensitive information:

- `DB_HOST`: Database host
- `DB_PORT`: Database port
- `DB_NAME`: Database name
- `DB_USER`: Database username
- `DB_PASS`: Database password
- `DB_CHARSET`: Database charset
- `FTP_SERVER`: FTP server address
- `FTP_USER`: FTP username
- `FTP_PASSWORD`: FTP password
- `FTP_PORT`: FTP port

Ensure these secrets are properly configured in the GitHub repository settings.
