<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rediseñada en 2026_04_21_000001_redesign_cuentas_cobrar_and_pagos.php
        if (Schema::hasTable('cuentas_cobrar')) return;

        Schema::create('cuentas_cobrar', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trabajo_id')
                ->constrained('trabajos')
                ->restrictOnDelete();

            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->restrictOnDelete();

            $table->decimal('monto_total', 12, 2);       // Espejo del trabajo para trazabilidad
            $table->decimal('monto_cobrado', 12, 2)->default(0);
            // saldo_pendiente se calcula: monto_total - monto_cobrado (no se almacena para evitar inconsistencias)

            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();

            $table->enum('estado', ['pendiente', 'parcial', 'pagada', 'vencida'])
                ->default('pendiente');

            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_cobrar');
    }
};
