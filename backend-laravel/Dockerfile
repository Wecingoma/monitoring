FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    nginx \
    postgresql-dev \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-install pdo_pgsql pgsql pcntl \
    && pecl install redis sodium \
    && docker-php-ext-enable redis sodium

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-interaction --optimize-autoloader --no-dev \
    && php artisan key:generate --force \
    && php artisan config:cache

RUN mkdir -p storage/logs \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]