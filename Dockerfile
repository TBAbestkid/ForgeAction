FROM php:8.2-apache

# Variáveis
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APACHE_DOCUMENT_ROOT=/var/www/html/public \
    PATH="$PATH:/var/www/html/vendor/bin"

# Instala extensões essenciais para Laravel + PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev zip unzip git curl libonig-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Instala Node.js (20.x)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Habilita mod_rewrite do Apache
RUN a2enmod rewrite \
    && sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copia Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Define diretório de trabalho
WORKDIR /var/www/html

# Copia somente os manifests para aproveitar cache
COPY composer.json composer.lock package.json package-lock.json* vite.config.* ./

# Instala dependências PHP e JS
RUN composer install --no-dev --optimize-autoloader --no-scripts \
    && npm install

# Copia o restante do projeto
COPY . .

# Build frontend com Vite (gera public/build) + autoload + discover
RUN npm run build \
    && composer dump-autoload \
    && php artisan package:discover

# Ajusta permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Copia entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Exposição da porta
EXPOSE 8080

# Define entrypoint e comando padrão
ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]
