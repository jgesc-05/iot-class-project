from base import Sensor
from profiles import PROFILES
import os

from dotenv import load_dotenv
load_dotenv()

# Instancias el sensor usando el "molde"
sensor_ph_sustrato = Sensor(
    device_id="sensor-ph",
    api_key=os.getenv("API_KEY_PH"),
    measurement="pH",
    **PROFILES['ph_sustrato']
)

sensor_ph_sustrato.run()