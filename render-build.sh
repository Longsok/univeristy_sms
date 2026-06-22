#!/usr/bin/env bash
set -e

echo "==> Installing dependencies..."
composer install --no-dev --optimize-autoloader

echo "==> Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Seeding admin..."
php artisan db:seed --class=AdminSeeder --force

echo "==> Linking storage..."
php artisan storage:link

echo "==> Optimizing..."
php artisan optimize

echo "==> Done!"