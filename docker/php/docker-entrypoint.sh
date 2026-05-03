#!/bin/sh
set -e

if [ ! -d "vendor" ]; then
    echo "Vendor folder is empty. Installing dependencies..."
    composer install --no-interaction --optimize-autoloader
else
    echo "Vendor folder exists. Syncing..."
    composer install --no-interaction --optimize-autoloader
fi

if [ ! -f .env ]; then
    cp .env.dist .env
fi

echo "Waiting for MySQL connection..."

php << 'EOF'
<?php
$host = getenv('DATABASE_HOST');
$db   = getenv('DATABASE_NAME');
$user = getenv('DATABASE_USER');
$pass = getenv('DATABASE_PASSWORD');

while (true) {
    try {
        new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        echo "Connection established!\n";
        break;
    } catch (PDOException $e) {
        echo "Database is still initializing (" . $e->getMessage() . ")\n";
        sleep(5);
    }
}
EOF

echo "MySQL is ready!"

echo "Running migrations..."
php bin/console doctrine:migrations:migrate

echo "Starting application..."

exec "$@"
