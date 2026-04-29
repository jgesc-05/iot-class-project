#!/bin/bash

# 1. Asegurar que si algo falla, el script se detenga
set -e

# 2. Activar el entorno (ajusta la ruta si es necesario)
source .venv/bin/activate

# 3. Cargar las llaves del .env como variables del sistema
export $(grep -v '^#' ../.env | xargs)

# 4. Crear carpeta para los reportes de error/salida si no existe
mkdir -p logs

# 5. Lanzar cada sensor. 
# El ">" guarda lo que el sensor imprime en un archivo.
# El "2>&1" guarda también los errores en ese mismo archivo.
# El "&" es la magia que los manda al fondo.

echo "Iniciando orquestación de 8 sensores..."

python temp_ambiente_a.py > logs/temp.log 2>&1 &
python humedad_ambiente_a.py > logs/hum_amb.log 2>&1 &
python humedad_sustrato_a.py > logs/suelo_a1.log 2>&1 &
python humedad_sustrato_b.py > logs/suelo_a2.log 2>&1 &
python co2.py > logs/co2.log 2>&1 &
python luminosidad.py > logs/lux.log 2>&1 &
python ph_sustrato.py > logs/ph.log 2>&1 &
python ec.py > logs/ec.log 2>&1 &

echo "¡Todos los sensores están corriendo en segundo plano!"
echo "Usa './stop_all.sh' para detenerlos o revisa la carpeta /logs para ver su actividad."
