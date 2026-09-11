#!/bin/bash
# Production Permissions Setup for SecuroFi.Tech (Linux / cPanel / VPS)
set -e

echo "Setting standard production file and folder permissions..."

# Default baseline: 755 for directories, 644 for files
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# Writable directories for web server
chmod -R 775 storage bootstrap/cache

# Strict privacy for environment file
if [ -f .env ]; then
    chmod 600 .env
fi

# Executable binaries and deployment scripts
chmod +x artisan deploy.sh set-permissions.sh

echo "Permissions configured successfully!"
echo "  - Directories: 755"
echo "  - Files: 644"
echo "  - storage & bootstrap/cache: 775"
echo "  - .env: 600"
