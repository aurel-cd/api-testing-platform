#!/bin/bash

# Optimize the Laravel app
echo "Optimizing the Laravel app..."
php artisan optimize

echo "Migrating new tables..."
php artisan migrate --force

# access storage folder
chmod -R 777 ./storage

# Final step, start the Apache server
echo "Starting Apache server..."
apache2-foreground
