FROM php:8.4-apache-bookworm

# Install ekstensi PHP yang dibutuhkan Laravel dan MySQL
RUN apt-get update && apt-get install -y git unzip libzip-dev libonig-dev \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer versi tertentu
COPY --from=composer:2.8.11 /usr/bin/composer /usr/bin/composer

# Atur direktori kerja Laravel
WORKDIR /var/www/html

# Salin file dependency terlebih dahulu untuk membantu caching
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-scripts \
    --no-autoloader

# Salin seluruh source code backend
COPY . .

# Buat autoloader dan jalankan package discovery Laravel
RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data storage bootstrap/cache

# Arahkan Apache ke folder public Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri \
    -e 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf

EXPOSE 80
