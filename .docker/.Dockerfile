FROM php:7.4-apache

# Install dependencies and GD extension
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libmariadb-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli pdo_mysql zip \
    && docker-php-ext-enable gd mysqli pdo_mysql

# Enable mod_rewrite for Apache
RUN a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html
