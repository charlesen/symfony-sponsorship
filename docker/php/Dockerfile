FROM php:8.2-fpm

# Mise à jour et installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
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
    unzip \
    procps \
    && rm -rf /var/lib/apt/lists/*

# Configuration du timezone
ENV TZ=Europe/Paris
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Installation des extensions PHP
RUN apt-get update && apt-get install -y libzip-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install -j$(nproc) zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mysqli intl xsl bcmath soap sockets opcache pcntl

# Installation de Redis et autres extensions PECL
RUN pecl install redis && docker-php-ext-enable redis

# Activation des extensions
RUN docker-php-ext-enable opcache pdo_mysql redis pcntl

# Installation des outils système supplémentaires
RUN apt-get update && apt-get install -y \
    procps \
    && rm -rf /var/lib/apt/lists/*

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Installation de symfony/redis-messenger
RUN composer require symfony/redis-messenger --no-scripts --no-interaction

# Installation de Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g yarn

# Configuration PHP
RUN { \
    echo 'memory_limit = 512M'; \
    echo 'upload_max_filesize = 100M'; \
    echo 'post_max_size = 108M'; \
    echo 'max_execution_time = 300'; \
    echo 'max_input_time = 300'; \
    echo 'date.timezone = Europe/Paris'; \
    echo 'disable_functions = '; \
    echo 'allow_url_fopen = On'; \
    echo 'allow_url_include = Off'; \
} > /usr/local/etc/php/conf.d/custom.ini

# Suppression du fichier opcache.ini existant pour éviter les doublons
RUN rm -f /usr/local/etc/php/conf.d/opcache.ini

# Configuration des permissions
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data \
    && mkdir -p /var/www/.composer \
    && chown -R www-data:www-data /var/www

# Définition du répertoire de travail
WORKDIR /var/www/html

# Commande par défaut
CMD ["php-fpm"]
