FROM php:7.4-apache

# Cài đặt các gói phụ thuộc
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libmariadb-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install mysqli pdo_mysql zip \
    && docker-php-ext-enable mysqli pdo_mysql

# Bật mod_rewrite của Apache
RUN a2enmod rewrite

# Cài đặt Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Thiết lập thư mục làm việc
WORKDIR /var/www/html
