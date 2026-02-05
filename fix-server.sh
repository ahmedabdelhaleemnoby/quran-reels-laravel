#!/bin/bash

echo "=========================================="
echo "Laravel Server Fix Script"
echo "=========================================="
echo ""

# Fix permissions
echo "[1/6] Fixing permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
echo "✓ Permissions fixed"
echo ""

# Clear all caches
echo "[2/6] Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "✓ Caches cleared"
echo ""

# Generate app key if missing
echo "[3/6] Checking application key..."
if grep -q "APP_KEY=$" .env || ! grep -q "APP_KEY=" .env; then
    php artisan key:generate --force
    echo "✓ Application key generated"
else
    echo "✓ Application key already exists"
fi
echo ""

# Create sessions table
echo "[4/6] Creating database tables..."
php artisan session:table 2>/dev/null || echo "Session table migration already exists"
php artisan cache:table 2>/dev/null || echo "Cache table migration already exists"
php artisan queue:table 2>/dev/null || echo "Queue table migration already exists"
echo ""

# Run migrations
echo "[5/6] Running migrations..."
php artisan migrate --force
echo "✓ Migrations completed"
echo ""

# Optimize for production
echo "[6/6] Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✓ Optimization completed"
echo ""

echo "=========================================="
echo "✓ All fixes applied successfully!"
echo "=========================================="
echo ""
echo "If the site still doesn't work, check:"
echo "1. storage/logs/laravel.log"
echo "2. PHP version (needs 8.2+)"
echo "3. Database credentials in .env"
