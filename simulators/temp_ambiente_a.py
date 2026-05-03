
from base import Sensor
from profiles import PROFILES
import os

from dotenv import load_dotenv
load_dotenv()


# Instancias el sensor usando el "molde"
sensor_ambiente_a = Sensor(
    device_id="sensor-temp-a",
    api_key=os.getenv("API_KEY_TEMP"),
    measurement="temperatura_ambiente",
    **PROFILES['temperatura_ambiente']
)

sensor_ambiente_a.run()