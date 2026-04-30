import os
import csv
import requests
from dotenv import load_dotenv

load_dotenv()

API_URL = os.getenv("API_URL")

with open('dataset_7d.csv') as f:
    reader = csv.DictReader(f)
    fila_actual = 0
    for row in reader:
        for (did, measurement, value, unit, key_env) in [
            ('dataset-temp', 'temperatura_ambiente', row['temperatura'], '°C', 'API_KEY_DS_TEMP'),
            ('dataset-ec',   'ec',                   row['ec'],          'mS/cm', 'API_KEY_DS_EC'),
        ]:
            requests.post(f'{API_URL}/metrics', json={
               'device_id': did,
               'api_key': os.getenv(key_env),
               'measurement': measurement,
               'value': float(value),
               'unit': unit,
               'timestamp': row['timestamp'],
            })

            fila_actual += 1
            if fila_actual % 100 == 0:
                print(f"🚀 Procesadas {fila_actual} filas...")

print('dataset cargado')


