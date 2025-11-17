FROM php:8.3-cli

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first
COPY composer.json composer.lock /var/www/

# Install PHP dependencies
RUN composer install --no-interaction --no-dev --prefer-dist --no-scripts --no-autoloader

# Copy existing application directory
COPY . /var/www

# Complete composer installation
RUN composer dump-autoload --no-dev --optimize

# Copy existing application directory permissions
RUN chown -R www-data:www-data /var/www

# Change current user to www
USER www-data

# Expose port 8000
EXPOSE 8000

# Start Laravel development server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
