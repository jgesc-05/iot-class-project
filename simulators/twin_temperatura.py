"""
Digital twin de sensor-temp-a.

Lee el ultimo valor del sensor real cada 30 segundos, le suma un offset
de calibracion (+0.5°C) y reporta como dispositivo twin-temperatura.

Ejemplo de uso de un digital twin: representar un sensor "calibrado
distinto" sin alterar el sensor real. En produccion seria util para
testear correcciones de calibracion antes de aplicarlas al hardware.
"""
import os
import time
import requests
import sys
from datetime import datetime, timezone
from dotenv import load_dotenv

load_dotenv()

API_URL = os.getenv('API_URL', 'http://localhost:8000/api')
API_KEY = os.getenv('API_KEY_TWIN_TEMP')

if not API_KEY:
    print('ERROR: falta API_KEY_TWIN_TEMP en .env', file=sys.stderr)
    sys.exit(1)

SOURCE_DEVICE = 'sensor-temp-a'
TWIN_DEVICE = 'twin-temp'
MEASUREMENT = 'temperatura_ambiente'
UNIT = '°C'
INTERVAL = 30
OFFSET = 0.5


def read_source() -> float | None:
    """Consulta el endpoint publico para obtener el ultimo valor del sensor real."""
    r = requests.get(f'{API_URL}/devices/{SOURCE_DEVICE}/latest', timeout=5)
    r.raise_for_status()
    data = r.json()
    return data.get('value')


def post_metric(value: float):
    """Envia el valor del twin (con offset aplicado) al endpoint."""
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
    print(f'[{TWIN_DEVICE}] offset aplicado: +{OFFSET} {UNIT}')
    while True:
        try:
            source_value = read_source()
            if source_value is None:
                print(f'[{TWIN_DEVICE}] sensor real sin datos aun, esperando...')
            else:
                twin_value = source_value + OFFSET
                post_metric(twin_value)
                print(f'[{TWIN_DEVICE}] {twin_value} {UNIT} (real: {source_value})')
        except requests.exceptions.RequestException as e:
            print(f'[{TWIN_DEVICE}] ERROR HTTP: {e}', file=sys.stderr)
        except Exception as e:
            print(f'[{TWIN_DEVICE}] ERROR: {e}', file=sys.stderr)
        time.sleep(INTERVAL)


if __name__ == '__main__':
    main()
