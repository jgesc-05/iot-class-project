
import time
import math
import random
import requests
import os
from dotenv import load_dotenv
from datetime import datetime, timezone

load_dotenv()

API_URL = os.getenv("API_URL", "http://localhost:8000/api").rstrip("/")

class Sensor:
    def __init__(self, device_id, api_key, measurement, base, amplitude, noise, unit='', interval=5, offset=0.0, active=True):
        self.device_id = device_id
        self.api_key = api_key
        self.measurement = measurement
        self.base = base
        self.amplitude = amplitude
        self.noise = noise
        self.unit = unit
        self.interval = interval
        self.url = f'{API_URL}/metrics'
        self.offset = offset
        self.active = active
        self.last_command_check = 0
        self.command_check_interval = 15


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



    def poll_commands(self):
       url = f"{API_URL}/devices/{self.device_id}/commands"
       headers = {
          "Authorization": f"Bearer {self.api_key}",
          "Content-Type": "application/json"
          }

       try:
           r = requests.get(url, headers=headers, timeout=5)

           if r.status_code == 200:
               data = r.json()
               commands = data.get("commands", [])

               for cmd in commands:
                   self.apply(cmd)

           elif r.status_code == 401:
               print(f"[{self.device_id}] Error: Token de API inválido.")

           else:
               print(f"[{self.device_id}] Error del servidor: {r.status_code}")

       except Exception as e:
           print(f"[{self.device_id}] Error de conexión en polling: {e}")


    def apply(self, cmd):
        try:
            # 1. Extraemos el tipo y el payload según la estructura del JSON del contrato
            cmd_type = cmd.get("type")
            payload = cmd.get("payload", {})
            cmd_id = cmd.get("id")

            print(f"[{self.device_id}] Procesando comando {cmd_id}: {cmd_type}")

            if cmd_type == "on_off":
                # Usamos el valor explícito que envía el backend
                self.active = payload.get("on", self.active)

            elif cmd_type == "set_interval":
                self.interval = payload.get("seconds", self.interval)

            elif cmd_type == "calibrate_offset":
                self.offset = payload.get("offset", self.offset)

            else:
                # Si el tipo no es conocido, reportamos falla
                print(f"[{self.device_id}] Tipo de comando desconocido: {cmd_type}")
                self.ack(cmd_id, "failed", {"reason": "unknown_type"})
                return

            # 2. Si todo salió bien, confirmamos ejecución exitosa
            self.ack(cmd_id, "executed")

        except Exception as e:
            print(f"[{self.device_id}] Error al aplicar comando: {e}")
            # Es vital avisar al backend que falló para que no se quede en 'pending'
            self.ack(cmd.get("id"), "failed", {"reason": str(e)})



    def ack(self, command_id, status, result=None):
        url = f"{API_URL}/devices/{self.device_id}/commands/{command_id}/ack"
        headers = {"Authorization": f"Bearer {self.api_key}"}
        body = {"status": status}
        if result:
            body["result"] = result

        try:
            r = requests.patch(url, headers=headers, json=body, timeout=5)
            if r.status_code == 204:
                print(f"[{self.device_id}] Comando {command_id} confirmado como {status}")
        except Exception as e:
            print(f"[{self.device_id}] Error al enviar ACK: {e}")



    def run(self):
        print(f"--- Sensor {self.device_id} iniciado ---")

        while True:
            now = time.time()

            if now - self.last_command_check >= self.command_check_interval:
                self.poll_commands()
                self.last_command_check = now  # usa el atributo de instancia

            if self.active:
                value = self.generate_value()
                valor_calibrado = value + self.offset
                self.send_value(valor_calibrado)
            else:
                print(f"[{self.device_id}] Sensor en pausa (active=False).")

            time.sleep(self.interval)

