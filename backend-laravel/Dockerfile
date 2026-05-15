FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

# Dépendances système
RUN apk add --no-cache \
    nginx \
    nodejs \
    npm \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    postgresql-dev \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS

# Extensions PHP
RUN docker-php-ext-install pdo pdo_pgsql pgsql pcntl zip

# Redis
RUN pecl install redis \
    && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier projet
COPY . .

# Installer dépendances backend
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Installer frontend
RUN npm install

# Build frontend Vite
RUN npm run build

# Permissions Laravel
RUN chmod -R 775 storage bootstrap/cache

# Générer caches Laravel
RUN php artisan config:clear \
    && php artisan cache:clear \
    && php artisan route:clear \
    && php artisan view:clear

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=$PORT