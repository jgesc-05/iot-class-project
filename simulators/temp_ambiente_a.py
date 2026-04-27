"""
Simulador básico de temperatura ambiente para sensor-temp-a.

Genera una temperatura realista (oscilación diaria + ruido) y la envía
al endpoint POST /api/metrics cada 15 segundos.

Este script es temporal — el día 5, el compañero (B) lo va a refactorizar
en una clase reutilizable. Por ahora solo necesitamos datos fluyendo
para construir el dashboard de Grafana.
"""
import os
import time
import math
import random
import requests
import sys
from datetime import datetime, timezone
from dotenv import load_dotenv

load_dotenv()

API_URL = os.getenv('API_URL', 'http://localhost:8000/api')
API_KEY = os.getenv('API_KEY_TEMP_A')

if not API_KEY:
    print('ERROR: falta API_KEY_TEMP_A en .env', file=sys.stderr)
    sys.exit(1)

DEVICE_ID = 'sensor-temp-a'
MEASUREMENT = 'temperatura_ambiente'
UNIT = '°C'
INTERVAL = 15  # segundos entre lecturas

# Parámetros de la oscilación
BASE = 22.0       # temperatura promedio (°C)
AMPLITUDE = 4.0   # variación día/noche (±4°C)
NOISE = 0.3       # ruido aleatorio (gaussiano)


def read_sensor() -> float:
    """Genera un valor de temperatura plausible para la hora actual."""
    h = datetime.now().hour + datetime.now().minute / 60
    # Pico al mediodía, mínimo a las 6 AM (offset de fase: -6 horas)
    daily = math.sin((h - 6) / 24 * 2 * math.pi)
    return round(BASE + AMPLITUDE * daily + random.gauss(0, NOISE), 2)


def post_metric(value: float):
    """Envía la métrica al endpoint."""
    payload = {
        'device_id': DEVICE_ID,
        'api_key': API_KEY,
        'measurement': MEASUREMENT,
        'value': value,
        'unit': UNIT,
        'timestamp': datetime.now(timezone.utc).isoformat(),
    }
    r = requests.post(f'{API_URL}/metrics', json=payload, timeout=5)
    r.raise_for_status()


def main():
    print(f'[{DEVICE_ID}] simulador iniciado, intervalo {INTERVAL}s')
    print(f'[{DEVICE_ID}] Ctrl+C para detener')
    while True:
        try:
            value = read_sensor()
            post_metric(value)
            print(f'[{DEVICE_ID}] {value} {UNIT}')
        except requests.exceptions.RequestException as e:
            print(f'[{DEVICE_ID}] ERROR HTTP: {e}', file=sys.stderr)
        except Exception as e:
            print(f'[{DEVICE_ID}] ERROR: {e}', file=sys.stderr)
        time.sleep(INTERVAL)


if __name__ == '__main__':
    main()
