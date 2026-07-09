#!/bin/sh

# Fix permissions at runtime for mounted volumes
chown -R www-data:www-data /var/www/html/database/tenants /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/database/tenants /var/www/html/storage /var/www/html/bootstrap/cache

# Execute the main command
exec "$@"