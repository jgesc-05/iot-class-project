# Plataforma IoT — Invernadero de Rosas

Proyecto académico para la clase de IoT.

Plataforma propia desplegada en la nube para monitoreo y control de un invernadero simulado de rosas de corte para exportación. El usuario registra dispositivos, recibe métricas en tiempo real, visualiza históricos y envía comandos de retroceso.

## Stack

- **Backend:** Laravel 11 + Breeze (Livewire)
- **Base de datos:** PostgreSQL 16 + TimescaleDB
- **Visualización:** Grafana OSS embebido
- **Simuladores:** Python 3.11
- **Orquestación:** Docker Compose

## Estructura
## Arranque rápido

*Instrucciones completas se agregarán cuando el stack esté listo.*

```bash
docker compose up -d
docker compose exec app php artisan migrate --seed
```

## Equipo

Proyecto de 2 personas:
- **A:** Backend e infraestructura (Leydy Macareo)
- **B:** Frontend y simuladores
