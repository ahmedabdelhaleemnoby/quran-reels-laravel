FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
  libpng-dev \
  libjpeg-turbo-dev \
  freetype-dev \
  zip \
  libzip-dev \
  unzip \
  git \
  oniguruma-dev \
  postgresql-dev \
  libxml2-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql mbstring gd zip bcmath soap

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . .

# Install dependencies
RUN composer install --no-interaction --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
