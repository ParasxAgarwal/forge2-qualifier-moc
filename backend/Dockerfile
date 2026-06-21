FROM php:8.3-cli
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_sqlite zip
WORKDIR /app
COPY . .
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-scripts --no-interaction || composer update --no-scripts --no-interaction
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]