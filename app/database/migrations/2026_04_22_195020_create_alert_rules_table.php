<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('measurement', 64);
            $table->double('min_threshold')->nullable();
            $table->double('max_threshold')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->index(['device_id', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_rules');
    }
};
