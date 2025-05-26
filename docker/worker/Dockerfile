FROM php:8.2-fpm

# Install supervisor and other required packages
RUN apt-get update && apt-get install -y \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql
RUN pecl install redis && docker-php-ext-enable redis

# Create necessary directories
RUN mkdir -p /var/log/supervisor \
    && mkdir -p /var/run/supervisor

# Set permissions
RUN chown -R www-data:www-data /var/log/supervisor /var/run/supervisor /var/www/html \
    && chmod -R 775 /var/log/supervisor /var/run/supervisor \
    && chmod -R 755 /var/www/html

# Copy supervisor config
COPY docker/worker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set working directory
WORKDIR /var/www/html

# Switch to www-data user
USER www-data

# Start supervisor
CMD ["supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
