from base import Sensor
from profiles import PROFILES
import os

from dotenv import load_dotenv
load_dotenv()

# Instancias el sensor usando el "molde"
sensor_co2 = Sensor(
    device_id="sensor-co2",
    api_key=os.getenv("API_KEY_CO2"),
    measurement="ppm",
    **PROFILES['co2']
)

sensor_co2.run()