from base import Sensor
from profiles import PROFILES
import os

from dotenv import load_dotenv
load_dotenv()

# Instancias el sensor usando el "molde"
sensor_ec = Sensor(
    device_id="sensor-ec",
    api_key=os.getenv("API_KEY_EC"),
    measurement="mS/cm",
    **PROFILES['ec']
)

sensor_ec.run()