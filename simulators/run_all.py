"""
Simulador completo del invernadero de rosas Freedom/Explorer.

Levanta todos los sensores reales y gemelos digitales en hilos separados.
Lee las API keys desde docs/DEV_CREDENTIALS.md (generado por el seeder).

Uso:
    cd simulators/
    API_URL=http://saturnovm.centralus.cloudapp.azure.com:8000/api python run_all.py
"""

import os
import sys
import time
import threading
import requests
from datetime import datetime, timezone
from dotenv import load_dotenv

load_dotenv()

# --- Configuracion ---

API_URL = os.getenv('API_URL', 'http://localhost:8000/api').rstrip('/')
CREDENTIALS_PATH = os.getenv(
    'CREDENTIALS_PATH',
    os.path.join(os.path.dirname(__file__), '..', 'docs', 'DEV_CREDENTIALS.md')
)

# Mapeo de device_id -> (measurement, profile_key)
# Cada sensor del invernadero con su tipo de medicion
SENSORS = {
    # Pasillo central
    'sensor-temp-amb':     ('temperatura_ambiente', 'temperatura_ambiente'),
    'sensor-hum-amb':      ('humedad_ambiente',     'humedad_ambiente'),
    'sensor-co2':          ('co2',                  'co2'),
    'sensor-lux':          ('luminosidad',          'luminosidad'),
    # Cama A (norte)
    'sensor-suelo-a':      ('humedad_suelo',        'humedad_suelo'),
    'sensor-temp-suelo-a': ('temperatura_suelo',    'temperatura_suelo'),
    'sensor-ph-a':         ('ph_agua',              'ph_agua'),
    'sensor-color-a':      ('color_boton',          'color_boton'),
    'sensor-altura-a':     ('altura_tallo',         'altura_tallo'),
    # Cama B (sur)
    'sensor-suelo-b':      ('humedad_suelo',        'humedad_suelo'),
    'sensor-temp-suelo-b': ('temperatura_suelo',    'temperatura_suelo'),
    'sensor-ph-b':         ('ph_agua',              'ph_agua'),
    'sensor-color-b':      ('color_boton',          'color_boton'),
    'sensor-altura-b':     ('altura_tallo',         'altura_tallo'),
    # Extractores (4 esquinas)
    'sensor-ext-ne':       ('corriente_extractor',  'corriente_extractor'),
    'sensor-ext-no':       ('corriente_extractor',  'corriente_extractor'),
    'sensor-ext-se':       ('corriente_extractor',  'corriente_extractor'),
    'sensor-ext-so':       ('corriente_extractor',  'corriente_extractor'),
    # Temperatura foliar
    'sensor-foliar-n':     ('temperatura_foliar',   'temperatura_foliar'),
    'sensor-foliar-s':     ('temperatura_foliar',   'temperatura_foliar'),
}

# Gemelos digitales
TWINS = {
    'twin-temp': {
        'source': 'sensor-temp-amb',
        'measurement': 'temperatura_ambiente',
        'unit': '°C',
        'offset': 0.5,
        'interval': 30,
    },
    'twin-hum': {
        'source': 'sensor-hum-amb',
        'measurement': 'humedad_ambiente',
        'unit': '%',
        'offset': -1.0,
        'interval': 30,
    },
}


# --- Perfiles (importados) ---

from profiles import PROFILES


# --- Funciones auxiliares ---

def load_credentials(path):
    """Lee DEV_CREDENTIALS.md y extrae device_id -> api_key."""
    keys = {}
    try:
        with open(path) as f:
            for line in f:
                line = line.strip()
                if line.startswith('sensor-') or line.startswith('twin-'):
                    parts = line.split(': ', 1)
                    if len(parts) == 2:
                        keys[parts[0]] = parts[1]
    except FileNotFoundError:
        print(f'ERROR: No se encontro {path}', file=sys.stderr)
        print('Ejecuta primero: docker compose exec app php artisan db:seed', file=sys.stderr)
        sys.exit(1)
    return keys


# --- Sensor simulado (hilo) ---

import math
import random


def sensor_loop(device_id, api_key, measurement, profile, interval=15):
    """Hilo que simula un sensor enviando datos periodicamente."""
    base = profile['base']
    amplitude = profile['amplitude']
    noise = profile['noise']
    unit = profile['unit']
    url = f'{API_URL}/metrics'

    # Desfase aleatorio para que no todos los sensores oscilen igual
    phase_offset = random.uniform(0, 2 * math.pi)

    print(f'[{device_id}] Iniciado | {measurement} | {base}{unit} +-{amplitude}')

    while True:
        try:
            oscillation = amplitude * math.sin(time.time() / 60 + phase_offset)
            noise_val = random.uniform(-noise, noise)
            value = round(base + oscillation + noise_val, 2)

            payload = {
                'device_id': device_id,
                'api_key': api_key,
                'measurement': measurement,
                'value': value,
                'unit': unit,
                'timestamp': datetime.now(timezone.utc).isoformat(),
            }
            r = requests.post(url, json=payload, timeout=5)
            print(f'[{device_id}] {value} {unit} -> {r.status_code}')
        except Exception as e:
            print(f'[{device_id}] ERROR: {e}', file=sys.stderr)

        time.sleep(interval)


def twin_loop(device_id, api_key, config):
    """Hilo que simula un gemelo digital leyendo del sensor real."""
    source = config['source']
    measurement = config['measurement']
    unit = config['unit']
    offset = config['offset']
    interval = config['interval']

    print(f'[{device_id}] Twin iniciado | lee de {source} cada {interval}s')

    while True:
        try:
            r = requests.get(f'{API_URL}/devices/{source}/latest', timeout=5)
            r.raise_for_status()
            source_value = r.json().get('value')

            if source_value is not None:
                twin_value = round(source_value + offset, 2)
                payload = {
                    'device_id': device_id,
                    'api_key': api_key,
                    'measurement': measurement,
                    'value': twin_value,
                    'unit': unit,
                    'timestamp': datetime.now(timezone.utc).isoformat(),
                }
                r = requests.post(f'{API_URL}/metrics', json=payload, timeout=5)
                print(f'[{device_id}] {twin_value} {unit} (real: {source_value}) -> {r.status_code}')
            else:
                print(f'[{device_id}] Sin datos del sensor real aun')
        except Exception as e:
            print(f'[{device_id}] ERROR: {e}', file=sys.stderr)

        time.sleep(interval)


# --- Main ---

def main():
    print('=' * 60)
    print('Simulador Invernadero de Rosas - Freedom/Explorer')
    print(f'API: {API_URL}')
    print('=' * 60)

    keys = load_credentials(CREDENTIALS_PATH)
    print(f'Credenciales cargadas: {len(keys)} dispositivos')

    threads = []

    # Sensores reales
    for device_id, (measurement, profile_key) in SENSORS.items():
        api_key = keys.get(device_id)
        if not api_key:
            print(f'[{device_id}] SKIP - sin API key en credenciales')
            continue
        profile = PROFILES[profile_key]
        t = threading.Thread(
            target=sensor_loop,
            args=(device_id, api_key, measurement, profile),
            daemon=True,
        )
        t.start()
        threads.append(t)

    # Esperar 5s para que los sensores reales generen datos antes de los twins
    time.sleep(5)

    # Gemelos digitales
    for device_id, config in TWINS.items():
        api_key = keys.get(device_id)
        if not api_key:
            print(f'[{device_id}] SKIP - sin API key en credenciales')
            continue
        t = threading.Thread(
            target=twin_loop,
            args=(device_id, api_key, config),
            daemon=True,
        )
        t.start()
        threads.append(t)

    print(f'\n{len(threads)} sensores corriendo. Ctrl+C para detener.\n')

    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        print('\nDetenido.')


if __name__ == '__main__':
    main()
