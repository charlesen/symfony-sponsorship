FROM php:8.2-fpm

# Set timezone
ENV TZ=Europe/Paris

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libxslt1-dev \
    libpq-dev \
    zip \
    procps \
    && rm -rf /var/lib/apt/lists/*

# Configure timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        gd \
        intl \
        mysqli \
        opcache \
        pcntl \
        pdo \
        pdo_mysql \
        soap \
        sockets \
        xsl \
        zip \
    && docker-php-ext-configure zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g yarn

# Create PHP configuration directory
RUN mkdir -p /usr/local/etc/php/conf.d

# Remove any existing opcache.ini to avoid conflicts
RUN rm -f /usr/local/etc/php/conf.d/opcache.ini

# Set working directory and permissions
WORKDIR /var/www
RUN mkdir -p /var/www/.composer \
    && mkdir -p /var/www/var/sessions \
    && chown -R www-data:www-data /var/www/.composer \
    && chown -R www-data:www-data /var/www/html \
    && chown -R www-data:www-data /var/www/var/sessions \
    && chmod -R 770 /var/www/var/sessions

# Clean up
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Default command
CMD ["php-fpm"]
