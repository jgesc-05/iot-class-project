<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'device_id' => 'dev-'.fake()->unique()->numerify('######'),
            'type' => fake()->randomElement(['real', 'twin', 'api', 'dataset']),
            'measurement' => fake()->randomElement([
                'temperatura_ambiente',
                'humedad_ambiente',
                'humedad_sustrato',
                'co2',
                'luminosidad',
                'ph_sustrato',
                'ec',
            ]),
            'unit' => '°C',
            'api_key_hash' => hash('sha256', fake()->uuid()),
            'sample_interval_s' => 15,
            'metadata' => null,
        ];
    }
}
