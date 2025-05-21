FROM php:8.2-fpm

ARG TIMEZONE

# Définition du timezone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone \
    && printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini

# Installation des dépendances système
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    gnupg \
    g++ \
    procps \
    openssl \
    git \
    unzip \
    zlib1g-dev \
    libzip-dev \
    libfreetype6-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libicu-dev \
    libonig-dev \
    libxslt1-dev \
    libxml2-dev \
    libpq-dev \
    acl \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/* \
    && echo 'alias sf="php bin/console"' >> ~/.bashrc

# Configuration et installation des extensions PHP
RUN docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mysqli \
        zip \
        xsl \
        gd \
        intl \
        opcache \
        exif \
        mbstring \
        bcmath \
        soap \
        sockets \
        xml \
    && docker-php-ext-enable pdo_mysql \
    && echo 'zend_extension=opcache.so' > /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.enable=1' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.enable_cli=1' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.memory_consumption=128' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.interned_strings_buffer=8' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.max_accelerated_files=4000' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.revalidate_freq=60' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.fast_shutdown=1' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.enable_file_override=1' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.jit=1255' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.jit_buffer_size=128M' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'memory_limit=512M' > /usr/local/etc/php/conf.d/memory-limit.ini \
    && echo 'upload_max_filesize=100M' > /usr/local/etc/php/conf.d/upload-limit.ini \
    && echo 'post_max_size=108M' >> /usr/local/etc/php/conf.d/upload-limit.ini \
    && echo 'max_execution_time=300' > /usr/local/etc/php/conf.d/time-limit.ini \
    && echo 'max_input_time=300' >> /usr/local/etc/php/conf.d/time-limit.ini \
    && echo 'date.timezone=Europe/Paris' > /usr/local/etc/php/conf.d/timezone.ini \
    && echo 'allow_url_fopen=1' > /usr/local/etc/php/conf.d/url-fopen.ini \
    && echo 'display_errors=On' > /usr/local/etc/php/conf.d/display-errors.ini \
    && echo 'log_errors=On' >> /usr/local/etc/php/conf.d/display-errors.ini \
    && echo 'error_log=/var/log/php_error.log' >> /usr/local/etc/php/conf.d/display-errors.ini

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration des permissions
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data \
    && mkdir -p /var/www/.composer \
    && chown -R www-data:www-data /var/www

# Définition du répertoire de travail
WORKDIR /var/www/html

# Définition de l'utilisateur
USER www-data

# Commande par défaut
CMD ["php-fpm"]

# Installation de Node.js et Yarn (en tant que root)
USER root
RUN apt-get update && apt-get install -y curl \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g yarn \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Configuration des variables d'environnement pour MySQL
ENV MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD:-root}
ENV MYSQL_DATABASE=${MYSQL_DATABASE:-symfony_sponsorship}
ENV MYSQL_USER=${MYSQL_USER:-app}
ENV MYSQL_PASSWORD=${MYSQL_PASSWORD:-app}

# Définition du répertoire de travail
WORKDIR /var/www/html

# Exposer le port 9000 pour PHP-FPM
EXPOSE 9000

# Installation des extensions PECL
RUN pecl install -o -f redis \
    && pecl install xdebug \
    && docker-php-ext-enable redis xdebug

# Configuration de PHP
RUN { \
    echo 'memory_limit = 512M'; \
    echo 'upload_max_filesize = 100M'; \
    echo 'post_max_size = 100M'; \
    echo 'max_execution_time = 600'; \
    echo 'max_input_vars = 3000'; \
    echo 'date.timezone = ${TIMEZONE}'; \
} > /usr/local/etc/php/conf.d/php.ini

# Nettoyage
RUN apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

WORKDIR /var/www/html

# Définir les permissions pour l'utilisateur www-data
RUN usermod -u 1000 www-data \
    && groupmod -g 1000 www-data \
    && chown -R www-data:www-data /var/www

# Commande par défaut
CMD ["php-fpm"]
