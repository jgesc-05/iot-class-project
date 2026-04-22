<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->enum('type', ['on_off', 'set_interval', 'calibrate_offset']);
            $table->jsonb('payload')->nullable();
            $table->enum('status', ['pending', 'executed', 'failed'])->default('pending');
            $table->timestampTz('acked_at')->nullable();
            $table->jsonb('result')->nullable();
            $table->timestamps();

            $table->index(['device_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commands');
    }
};
