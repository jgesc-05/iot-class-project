from base import Sensor
from profiles import PROFILES
import os

from dotenv import load_dotenv
load_dotenv()

# Instancias el sensor usando el "molde"
sensor_humedad_sustrato_a = Sensor(
    device_id="sensor-suelo-a1",
    api_key=os.getenv("API_KEY_SUELO_A1"),
    measurement="%",
    **PROFILES['humedad_sustrato']
)

sensor_humedad_sustrato_a.run()