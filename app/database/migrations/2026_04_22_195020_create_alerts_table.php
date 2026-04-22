<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_rule_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('device_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->timestampTz('triggered_at');
            $table->double('value');
            $table->timestampTz('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['device_id', 'resolved_at']);
            $table->index('triggered_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
