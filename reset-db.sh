#!/bin/bash
# Resetea ambas bases de datos: iot (desarrollo) e iot_testing (tests).
# Necesario por incompatibilidad de TimescaleDB con migrate:fresh.

set -e

echo "→ Reseteando base de desarrollo (iot)..."
docker compose exec db psql -U iot -d postgres -c "DROP DATABASE IF EXISTS iot WITH (FORCE);" > /dev/null
docker compose exec db psql -U iot -d postgres -c "CREATE DATABASE iot OWNER iot;" > /dev/null
docker compose exec app php artisan migrate --seed --force
echo "✓ Base de desarrollo reseteada con seeder (14 dispositivos)"

echo ""
echo "→ Reseteando base de tests (iot_testing)..."
docker compose exec db psql -U iot -d postgres -c "DROP DATABASE IF EXISTS iot_testing WITH (FORCE);" > /dev/null
docker compose exec db psql -U iot -d postgres -c "CREATE DATABASE iot_testing OWNER iot;" > /dev/null
docker compose exec -e APP_ENV=testing app php artisan migrate --force
echo "✓ Base de tests reseteada (sin seeder)"

echo ""
echo "✓ Listo. Para correr los tests: docker compose exec app ./vendor/bin/pest"
