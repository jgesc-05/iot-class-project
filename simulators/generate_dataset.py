"""
Generador de dataset historico de 7 dias.

Crea simulators/dataset_7d.csv con 10080 filas (1 por minuto durante 7 dias)
representando dos variables agronomicas plausibles:

- temperatura: oscilacion diaria senoidal (pico al mediodia, minimo a las 6 AM)
              con ruido gaussiano. Rango aproximado: 15-28°C.
- ec (conductividad electrica del sustrato): tendencia ligeramente decreciente
      a lo largo de la semana (simula desgaste nutricional sin fertilizacion)
      con ruido. Rango aproximado: 1.5-2.5 mS/cm.

Este script se ejecuta una sola vez. El CSV resultante se commitea al repo.
El compañero usara este archivo el dia 6 para hacer replay (cargar todas
las filas en la base via POST /api/metrics).
"""
import csv
import math
import random
from datetime import datetime, timedelta, timezone


# Rango temporal: 7 dias completos terminando hace 1 dia
END = datetime.now(timezone.utc).replace(hour=0, minute=0, second=0, microsecond=0) - timedelta(days=1)
START = END - timedelta(days=7)
INTERVAL = timedelta(minutes=1)
OUTPUT_FILE = 'dataset_7d.csv'

# Parametros de temperatura
TEMP_BASE = 21.5
TEMP_AMPLITUDE = 6.0
TEMP_NOISE = 0.4

# Parametros de EC
EC_BASE = 2.2
EC_END = 1.6   # valor objetivo al final de los 7 dias (decae linealmente)
EC_NOISE = 0.05


def temperature_at(t: datetime) -> float:
    """Genera temperatura plausible para una hora del dia."""
    h = t.hour + t.minute / 60
    daily = math.sin((h - 6) / 24 * 2 * math.pi)
    return round(TEMP_BASE + TEMP_AMPLITUDE * daily + random.gauss(0, TEMP_NOISE), 2)


def ec_at(t: datetime, total_seconds: float) -> float:
    """Genera EC con tendencia decreciente a lo largo del periodo."""
    elapsed = (t - START).total_seconds() / total_seconds
    # interpolacion lineal de EC_BASE hasta EC_END
    base = EC_BASE + (EC_END - EC_BASE) * elapsed
    return round(base + random.gauss(0, EC_NOISE), 3)


def main():
    print(f'Generando dataset de {START.isoformat()} a {END.isoformat()}')
    total_rows = int((END - START).total_seconds() / INTERVAL.total_seconds())
    total_seconds = (END - START).total_seconds()
    print(f'Total esperado: {total_rows} filas (1 por minuto durante 7 dias)')

    with open(OUTPUT_FILE, 'w', newline='') as f:
        writer = csv.DictWriter(f, fieldnames=['timestamp', 'temperatura', 'ec'])
        writer.writeheader()

        t = START
        rows_written = 0
        while t < END:
            writer.writerow({
                'timestamp': t.isoformat(),
                'temperatura': temperature_at(t),
                'ec': ec_at(t, total_seconds),
            })
            t += INTERVAL
            rows_written += 1
            if rows_written % 1000 == 0:
                print(f'  {rows_written} filas escritas...')

    print(f'✓ {rows_written} filas escritas en {OUTPUT_FILE}')


if __name__ == '__main__':
    main()
