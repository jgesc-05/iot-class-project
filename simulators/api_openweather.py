import os
import time
import requests
from dotenv import load_dotenv

load_dotenv()


CITY = os.getenv('CITY', 'Bucaramanga')
OWM_KEY = os.getenv('OWM_KEY')
API_URL = os.getenv('API_URL')
LAT, LON = 7.0653, -73.0498

while True:
    try:

        r = requests.get('https://api.openweathermap.org/data/2.5/weather',
                                 params={'lat': LAT, 'lon': LON, 'appid': OWM_KEY, 'units': 'metric'})
        data = r.json()

        # Extraer valores
        temp = data['main']['temp']
        hum = data['main']['humidity']


        metrics_to_send = [
            ('api-temp-ext', 'API_KEY_OWM_TEMP', temp, '°C', 'temperatura_exterior'),
            ('api-hum-ext', 'API_KEY_OWM_HUM', hum, '%', 'humedad_exterior'),
        ]

        for (did, key_env, val, unit, meas) in metrics_to_send:
            payload = {
                'device_id': did,
                'api_key': os.getenv(key_env),
                'measurement': meas,
                'value': val,
                'unit': unit
            }


            r = requests.post(f'{API_URL}/metrics', json=payload)
            print(f"Enviado {meas}: {val} {unit} - Status: {r.status_code}")

    except Exception as e:
        print(f"Error en el ciclo: {e}")


    time.sleep(300)


