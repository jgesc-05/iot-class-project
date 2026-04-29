"""
Digital twin de sensor-hum-a.

Lee el ultimo valor del sensor real cada 30 segundos, le agrega ruido
aleatorio (±2%) y reporta como dispositivo twin-humedad.

A diferencia del twin de temperatura (que aplica un offset fijo), este
twin simula incertidumbre estocastica como podria existir en un sensor
de humedad redundante con menor calidad.
"""
import os
import time
import random
import requests
import sys
from datetime import datetime, timezone
from dotenv import load_dotenv

load_dotenv()

API_URL = os.getenv('API_URL', 'http://localhost:8000/api')
API_KEY = os.getenv('API_KEY_TWIN_HUM')

if not API_KEY:
    print('ERROR: falta API_KEY_TWIN_HUM en .env', file=sys.stderr)
    sys.exit(1)

SOURCE_DEVICE = 'sensor-hum-a'
TWIN_DEVICE = 'twin-hum'
MEASUREMENT = 'humedad_ambiente'
UNIT = '%'
INTERVAL = 30
NOISE = 2.0


def read_source() -> float | None:
    r = requests.get(f'{API_URL}/devices/{SOURCE_DEVICE}/latest', timeout=5)
    r.raise_for_status()
    data = r.json()
    return data.get('value')


def post_metric(value: float):
    payload = {
        'device_id': TWIN_DEVICE,
        'api_key': API_KEY,
        'measurement': MEASUREMENT,
        'value': round(value, 2),
        'unit': UNIT,
        'timestamp': datetime.now(timezone.utc).isoformat(),
    }
    r = requests.post(f'{API_URL}/metrics', json=payload, timeout=5)
    r.raise_for_status()


def main():
    print(f'[{TWIN_DEVICE}] twin iniciado, leyendo {SOURCE_DEVICE} cada {INTERVAL}s')
    print(f'[{TWIN_DEVICE}] ruido aleatorio: ±{NOISE} {UNIT}')
    while True:
        try:
            source_value = read_source()
            if source_value is None:
                print(f'[{TWIN_DEVICE}] sensor real sin datos aun, esperando...')
            else:
                noise = random.uniform(-NOISE, NOISE)
                twin_value = source_value + noise
                # Limitar al rango fisico [0, 100] de humedad relativa
                twin_value = max(0, min(100, twin_value))
                post_metric(twin_value)
                print(f'[{TWIN_DEVICE}] {twin_value:.2f} {UNIT} (real: {source_value}, ruido: {noise:+.2f})')
        except requests.exceptions.RequestException as e:
            print(f'[{TWIN_DEVICE}] ERROR HTTP: {e}', file=sys.stderr)
        except Exception as e:
            print(f'[{TWIN_DEVICE}] ERROR: {e}', file=sys.stderr)
        time.sleep(INTERVAL)


if __name__ == '__main__':
    main()
