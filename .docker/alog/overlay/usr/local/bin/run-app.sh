#!/usr/bin/env bash

set -e

APP_ENV=${APP_ENV:-production}
CONTAINER_ROLE=${CONTAINER_ROLE:-app}

AUTOMIGRATE=${AUTOMIGRATE:-false}
DB_HOST=${DB_HOST:-mysql}
DB_PORT=${DB_PORT:-3306}

case "$CONTAINER_ROLE" in
    "queue")
        cp -ar /etc/services.d/worker /etc/service/worker
        ;;
    "scheduler")
        cp -ar /etc/services.d/scheduler /etc/service/scheduler
        ;;
    "app")
        cp -ar /etc/services.d/nginx /etc/service/nginx
        cp -ar /etc/services.d/php-fpm /etc/service/php-fpm
        ;;
esac

if [ "production" = "${APP_ENV}" ]; then
    echo "Caching configuration..."
    su -s /bin/bash -c 'cd /var/www/html && (php artisan config:cache || true)' www-data
    echo "Caching routes..."
    su -s /bin/bash -c 'cd /var/www/html && (php artisan route:cache  || true)' www-data
fi

if [ "$AUTOMIGRATE" = "true" ]; then
    echo "Waiting for database connection..."
    until nc -z -v -w30 $DB_HOST $DB_PORT
    do
        # wait for 2 seconds before check again
        echo "."
        sleep 2
    done

    php artisan migrate --force
fi

exec "$@"
