#!/bin/bash
# ==============================================================================
# SecuroFi.Tech — Automated Production Deployment Script
# ==============================================================================
set -e

echo "=== SecuroFi.Tech Production Deployment ==="

# 1. Apply secure permissions
echo "--> Setting permissions..."
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod -R 775 storage bootstrap/cache
if [ -f .env ]; then
    chmod 600 .env
fi
chmod +x artisan deploy.sh set-permissions.sh

# 2. Public storage symlink
echo "--> Linking storage..."
php artisan storage:link || true

# 3. Optimize Laravel Caches for Production
echo "--> Caching configuration, routes, views, and events..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "=================================================================="
echo "✓ SecuroFi.Tech is successfully deployed and optimized for production!"
echo "=================================================================="
