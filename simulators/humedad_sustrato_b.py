from base import Sensor
from profiles import PROFILES
import os

from dotenv import load_dotenv
load_dotenv()

# Instancias el sensor usando el "molde"
sensor_humedad_sustrato_b = Sensor(
    device_id="sensor-suelo-a2",
    api_key=os.getenv("API_KEY_SUELO_A2"),
    measurement="%",
    **PROFILES['humedad_sustrato']
)

sensor_humedad_sustrato_b.run()