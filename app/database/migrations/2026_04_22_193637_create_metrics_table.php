<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS timescaledb');

        Schema::create('metrics', function (Blueprint $table) {
            $table->timestampTz('time');
            $table->string('device_id', 64);
            $table->double('value');
            $table->jsonb('metadata')->nullable();

            $table->index(['device_id', 'time']);
        });

        DB::statement("
            SELECT create_hypertable(
                'metrics',
                'time',
                chunk_time_interval => INTERVAL '7 days'
            )
        ");

        DB::statement("
            SELECT add_retention_policy(
                'metrics',
                INTERVAL '14 days'
            )
        ");
    }

    public function down(): void
    {
        // Drop específico de hypertable antes del drop normal
        DB::statement('DROP TABLE IF EXISTS metrics CASCADE');
    }
};
