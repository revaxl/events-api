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
RUN apk add --no-cache \
    php8-common \
    php8-pdo \
    php8-opcache \
    php8-zip \
    php8-phar \
    php8-iconv \
    php8-cli \
    php8-curl \
    php8-openssl \
    php8-mbstring \
    php8-tokenizer \
    php8-fileinfo \
    php8-json \
    php8-xml \
    php8-xmlwriter \
    php8-simplexml \
    php8-dom \
    php8-pdo_mysql \
    php8-pdo_sqlite \
    php8-tokenizer
RUN ln -s /usr/bin/php8 /usr/bin/php

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy the files to the container
COPY . /app
COPY --from=vendor /app/vendor/ /app/vendor/
RUN composer dumpautoload

# Start the php server
CMD php -S 0.0.0.0:8000 -t /app/public
EXPOSE 8000
