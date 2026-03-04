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

# Habilita mod_rewrite e módulos de proxy
RUN a2enmod rewrite proxy proxy_http proxy_wstunnel

# Configura Apache para portas 8080
RUN echo "Listen 8080" > /etc/apache2/ports.conf \
    && sed -ri -e "s!<VirtualHost \*:80>!<VirtualHost *:8080>!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# === Adiciona configuração de proxy reverso ===
RUN echo '<VirtualHost *:8080>\n\
    ServerName localhost\n\
    DocumentRoot /var/www/html/public\n\
\n\
    # Proxy para API (acessa o container da API)\n\
    ProxyPass "/view"  "http://172.21.0.2:9001/api"\n\
    ProxyPassReverse "/view"  "http://172.21.0.2:9001/api"\n\
\n\
    # Proxy para WebSocket (acessa o container da API)\n\
    ProxyPass "/ws"  "ws://172.21.0.2:9001/ws"\n\
    ProxyPassReverse "/ws"  "ws://172.21.0.2:9001/ws"\n\
\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

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

# Debug: listar se ApiService existe
RUN ls -l /var/www/html/app/Services/ || echo "❌ Pasta Services não encontrada"
RUN test -f /var/www/html/app/Services/ApiService.php && echo "✅ ApiService.php existe" || echo "❌ ApiService.php não existe"

# Build frontend com Vite (gera public/build) + autoload + discover
RUN npm run build \
    && composer dump-autoload \
    && php artisan package:discover

# Ajusta permissões APENAS nas pastas essenciais
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copia entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Exposição das portas
EXPOSE 8080
EXPOSE 8443

# Define entrypoint e comando padrão
ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]
