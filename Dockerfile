# Install dependencies
FROM composer as vendor
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --no-autoloader
# Main container
FROM php:8-fpm-alpine
WORKDIR /app
# Create a production php configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
# Install php dependencies
RUN apk add --no-cache zip unzip curl sqlite

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy the files to the container
COPY . /app
COPY --from=vendor /app/vendor/ /app/vendor/
RUN composer dumpautoload

COPY .env.example .env

RUN touch /app/database/database.sqlite
RUN php artisan migrate && php artisan db:seed

# Start the php server
CMD php -S 0.0.0.0:$PORT -t /app/public
EXPOSE $PORT
