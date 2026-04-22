#!/bin/bash
# Resetea la base completa: borra, recrea y siembra.
# Necesario por incompatibilidad de TimescaleDB con migrate:fresh.

set -e

echo "→ Borrando base de datos iot..."
docker compose exec db psql -U iot -d postgres -c "DROP DATABASE IF EXISTS iot WITH (FORCE);" > /dev/null

echo "→ Recreando base de datos iot..."
docker compose exec db psql -U iot -d postgres -c "CREATE DATABASE iot OWNER iot;" > /dev/null

echo "→ Corriendo migraciones y seeder..."
docker compose exec app php artisan migrate --seed --force

echo "✓ Base reseteada"
