FROM php:8.3-apache

# Habilitar mod_rewrite e intl
RUN a2enmod rewrite

RUN apt-get update && \
    apt-get install -y \
    libzip-dev \
    libicu-dev \
    unzip \
    mariadb-client && \
    docker-php-ext-install intl pdo_mysql mysqli zip && \
    rm -rf /var/lib/apt/lists/*

# Configuração do VirtualHost para Laravel
COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# Definir diretório de trabalho
WORKDIR /var/www/html

# Copiar código
COPY . .

# Ajustar permissões (como a imagem roda com www-data, não precisa chown novamente)
RUN find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-interaction --optimize-autoloader \
    && composer clear-cache

EXPOSE 80
CMD ["apache2-foreground"]
