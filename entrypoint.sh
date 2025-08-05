#!/bin/bash

# Rodar comandos Laravel
php artisan storage:link
php artisan migrate --force

# Roda o Apache (passado via CMD)
exec "$@"
