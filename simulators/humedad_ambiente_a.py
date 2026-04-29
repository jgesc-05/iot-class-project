from base import Sensor
from profiles import PROFILES
import os

from dotenv import load_dotenv
load_dotenv()

# Instancias el sensor usando el "molde"
sensor_humedad_ambiente_a = Sensor(
    device_id="sensor-hum-a",
    api_key=os.getenv("API_KEY_HUM_AMB"),
    measurement="%",
    **PROFILES['humedad_ambiente']
)

sensor_humedad_ambiente_a.run()