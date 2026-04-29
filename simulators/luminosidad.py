from base import Sensor
from profiles import PROFILES
import os

from dotenv import load_dotenv
load_dotenv()

# Instancias el sensor usando el "molde"
sensor_luminosidad = Sensor(
    device_id="sensor-lux",
    api_key=os.getenv("API_KEY_LUX"),
    measurement="lux",
    **PROFILES['luminosidad']
)

sensor_luminosidad.run()