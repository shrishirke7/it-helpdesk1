FROM php:8.2-fpm

# Install system dependencies & LDAP extension for Active Directory login
RUN apt-get update && apt-get install -y \
    libldap2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install ldap gd

WORKDIR /var/www/html
