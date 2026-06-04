FROM php:8.1-apache

# Install PDO MySQL extensions for database secure queries
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache Mod Rewrite for clean routing
RUN a2enmod rewrite

# Copy local backend script files into the Apache work directory
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Expose port 80 for standard traffic
EXPOSE 80
