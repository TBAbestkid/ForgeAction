#!/bin/bash
set -e

php artisan key:generate

npm install --silent --no-progress --omit=dev
npm run build

# Rodar migrations (somente se banco estiver pronto)
php artisan migrate --force || true

# Limpa caches (agora em runtime, quando DB existe)
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan optimize:clear || true
php artisan key:generate

# Cria link de storage
php artisan storage:link || true

echo "EXTERNAL_API_URL=${EXTERNAL_API_URL:-172.17.0.2:9001}" >> .env
echo "EXTERNAL_API_USER=${EXTERNAL_API_USER:-admin}" >> .env
echo "EXTERNAL_API_PASS=${EXTERNAL_API_PASS:-admin}" >> .env

# Executa comando original (Apache)
exec "$@"
