#!/bin/bash
set -e

# Rodar migrations (somente se banco estiver pronto)
php artisan migrate --force || true

# Limpa caches (agora em runtime, quando DB existe)
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan optimize:clear || true

# Cria link de storage
php artisan storage:link || true

# Executa comando original (Apache)
exec "$@"
