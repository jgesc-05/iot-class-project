import os
import time
import requests
import random
from base import Sensor
from profiles import PROFILES
from dotenv import load_dotenv

load_dotenv()

class HumidityWithActuator(Sensor):
    # SOBRESCRIBIMOS EL MÉTODO READ
    def read(self):
        # 1. Intentamos obtener el valor base del perfil
        # Si tu clase base tiene un método para calcular el valor del perfil, úsalo.
        # Si no, generamos un valor aleatorio simple para la prueba:
        base_val = random.uniform(50, 55)

        API_URL = os.getenv('API_URL', 'http://localhost:8000')
        try:
            # CAMBIA 'sensor-suelo-a1' por el ID al que le enviaste el curl con el 1
            response = requests.get(f'{API_URL}/devices/sensor-suelo-a1/latest', timeout=2)

            if response.status_code == 200:
                data = response.json()
                # Verificamos si el valor es 1
                if data and float(data.get('value', 0)) == 1:
                    print(" DEBUG: Riego detectado ON (valor 1). Aumentando humedad...")
                    return round(base_val + 5.0, 2)
        except Exception as e:
            print(f" Error consultando actuador: {e}")

        return round(base_val, 2)

    def run(self):
        print(f" Sensor {self.device_id} activo...")
        while True:
            # Llamamos a TU método read()
            valor_a_enviar = self.read()

            # Usamos el método de la clase base para hacer el POST
            self.send_value(valor_a_enviar)

            time.sleep(self.interval) # Espera 10 segundos entre lecturas

# Ejecución
if __name__ == "__main__":
    sensor = HumidityWithActuator(
        device_id='sensor-suelo-a1',
        api_key=os.getenv('API_KEY_SUELO_A1'),
        measurement='humedad_sustrato',
        **PROFILES['humedad_sustrato']
    )
    sensor.run()