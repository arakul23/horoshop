#!/bin/sh
set -e

# Если папка vendor пуста, устанавливаем зависимости
if [ ! -d "vendor" ]; then
    echo "Vendor folder is empty. Installing dependencies..."
    composer install --no-interaction --optimize-autoloader
else
    # Если папка есть, можно просто докачать недостающее (опционально)
    echo "Vendor folder exists. Syncing..."
    composer install --no-interaction --optimize-autoloader
fi

# Выполняем основную команду контейнера (обычно это php-fpm)
exec "$@"