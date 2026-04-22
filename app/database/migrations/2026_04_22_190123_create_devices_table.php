<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('name');
            $table->string('device_id')->unique();
            $table->enum('type', ['real', 'twin', 'api', 'dataset']);
            $table->string('measurement', 64);
            $table->string('unit', 20);
            $table->string('api_key_hash', 64);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedInteger('sample_interval_s')->default(15);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index('api_key_hash');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
