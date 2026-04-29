#!/bin/bash
# Matar cualquier proceso de Python que tenga estos nombres en su comando
pkill -f 'python.*(temp|humedad|co2|luminosidad|ph|ec)'
echo "Sensores detenidos."
