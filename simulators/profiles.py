# Perfiles de sensores para invernadero de rosas Freedom/Explorer
# Rangos calibrados para contexto Bucaramanga, Colombia
#
# base = punto medio del rango operativo
# amplitude = mitad del rango de oscilacion (sinusoidal)
# noise = ruido gaussiano adicional
#
# La formula es: value = base + amplitude * sin(t/60) + random(-noise, noise)

PROFILES = {
    # DHT22 - Pasillo central | Rango Bucaramanga: 22-32°C
    'temperatura_ambiente': dict(base=27.0, amplitude=4.0, noise=0.5, unit='°C'),

    # DHT22 - Pasillo central | Rango Bucaramanga: 65-85%
    'humedad_ambiente': dict(base=75.0, amplitude=8.0, noise=1.5, unit='%'),

    # MH-Z19C | Rango Bucaramanga: 400-900 ppm
    'co2': dict(base=650, amplitude=200, noise=30, unit='ppm'),

    # BH1750FVI | Rango Bucaramanga: 20000-55000 lux
    'luminosidad': dict(base=37500, amplitude=15000, noise=1000, unit='lux'),

    # SEN0193 | Rango Bucaramanga: 1.6-2.2 V
    'humedad_suelo': dict(base=1.9, amplitude=0.25, noise=0.03, unit='V'),

    # DS18B20 | Rango Bucaramanga: 20-28°C
    'temperatura_suelo': dict(base=24.0, amplitude=3.0, noise=0.2, unit='°C'),

    # SEN0161-V2 | Rango Bucaramanga: 6.0-7.5 pH
    'ph_agua': dict(base=6.75, amplitude=0.6, noise=0.05, unit='pH'),

    # AS7341 | Rango Bucaramanga: 450-650 nm
    'color_boton': dict(base=550, amplitude=80, noise=10, unit='nm'),

    # VL53L1X | Rango Bucaramanga: 100-1500 mm
    'altura_tallo': dict(base=800, amplitude=500, noise=20, unit='mm'),

    # DFR0300 | Rango Bucaramanga: 3.0-10.0 A
    'corriente_extractor': dict(base=6.5, amplitude=3.0, noise=0.3, unit='A'),

    # MLX90640 | Rango Bucaramanga: 24-34°C
    'temperatura_foliar': dict(base=29.0, amplitude=4.0, noise=0.3, unit='°C'),
}
