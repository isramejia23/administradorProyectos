<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ampliar el enum estado_trabajo con 'solicitud' y 'rechazado'
        DB::statement("ALTER TABLE trabajos MODIFY COLUMN estado_trabajo ENUM('solicitud','pendiente','proceso','terminado','cancelado','rechazado') NOT NULL DEFAULT 'pendiente'");

        // Agregar columna para el motivo cuando una solicitud es rechazada
        Schema::table('trabajos', function (Blueprint $table) {
            $table->text('motivo_rechazo')->nullable()->after('estado_trabajo');
        });
    }

    public function down(): void
    {
        Schema::table('trabajos', function (Blueprint $table) {
            $table->dropColumn('motivo_rechazo');
        });

        DB::statement("ALTER TABLE trabajos MODIFY COLUMN estado_trabajo ENUM('pendiente','proceso','terminado','cancelado') NOT NULL DEFAULT 'pendiente'");
    }
};
