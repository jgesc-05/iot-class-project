
import time
import math
import random
import requests
import os
from dotenv import load_dotenv
from datetime import datetime, timezone



class Sensor:
    def __init__(self, device_id, api_key, measurement, base, amplitude, noise, unit='', interval=5):
        self.device_id = device_id
        self.api_key = api_key
        self.measurement = measurement
        self.base = base
        self.amplitude = amplitude
        self.noise = noise
        self.unit = unit
        self.interval = interval
        self.url = "http://localhost:8000/api/metrics"


    def generate_value(self):
        oscillation = self.amplitude * math.sin(time.time() / 60)
        noise_factor = random.uniform(-self.noise, self.noise)

        return round(self.base + oscillation + noise_factor, 2)

    def send_value(self, value):
        payload = {
        "device_id": self.device_id,
        "api_key": self.api_key,       # <--- Movido de headers a payload
        "measurement": self.measurement,
        "value": value,
        "unit": self.unit,             # <--- Agregado
        "timestamp": datetime.now(timezone.utc).isoformat()
        }


        try:
            response = requests.post(self.url, json=payload, timeout=5)
            print(f"[{self.device_id}] Enviado: {value} {self.unit} - Status: {response.status_code}")
        except Exception as e:
            print(f"[{self.device_id}] Error de conexión: {e}")

    def run(self):
            print(f"--- Sensor {self.device_id} iniciado ---")
            while True:
                value = self.generate_value()
                self.send_value(value)
                time.sleep(self.interval)