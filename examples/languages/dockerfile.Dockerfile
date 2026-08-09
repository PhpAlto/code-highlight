FROM composer:2 AS vendor
COPY composer.json composer.lock /app/
RUN composer install -d /app --no-dev --no-interaction
FROM php:8.4-cli-alpine
WORKDIR /app
COPY --from=vendor /app/vendor ./vendor
COPY . .
CMD ["php", "bin/highlight.php"]
