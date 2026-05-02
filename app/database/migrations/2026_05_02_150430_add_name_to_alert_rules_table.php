<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega columna name a alert_rules para que el operador pueda dar
     * un nombre legible a cada regla (ej: "Temperatura critica nocturna").
     *
     * Nullable para retrocompatibilidad con reglas existentes.
     * La UI valida que sea obligatorio al crear nuevas reglas.
     */
    public function up(): void
    {
        Schema::table('alert_rules', function (Blueprint $table) {
            $table->string('name', 200)->nullable()->after('device_id');
        });
    }

    public function down(): void
    {
        Schema::table('alert_rules', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
