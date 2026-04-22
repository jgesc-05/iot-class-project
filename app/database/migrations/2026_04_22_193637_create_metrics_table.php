<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Asegurar que la extensión TimescaleDB está activa.
        DB::statement('CREATE EXTENSION IF NOT EXISTS timescaledb');

        // 2. Crear la tabla con estructura normal de Postgres.
        Schema::create('metrics', function (Blueprint $table) {
            $table->timestampTz('time');
            $table->string('device_id', 64);
            $table->double('value');
            $table->jsonb('metadata')->nullable();

            $table->index(['device_id', 'time']);
        });

        // 3. Convertir la tabla normal en hypertable particionada por time.
        DB::statement("
            SELECT create_hypertable(
                'metrics',
                'time',
                chunk_time_interval => INTERVAL '7 days'
            )
        ");

        // 4. Política de retención: borrar datos de más de 14 días.
        DB::statement("
            SELECT add_retention_policy(
                'metrics',
                INTERVAL '14 days'
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('metrics');
    }
};
