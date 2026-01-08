
FROM php:8.2-cli-alpine

RUN apk add --no-cache \
    bash \
    git \
    unzip \
    libzip-dev \
    postgresql-dev

RUN docker-php-ext-install pdo pdo_pgsql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chmod +x /var/www/entrypoint.sh
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 10000

CMD ["/var/www/entrypoint.sh"]
