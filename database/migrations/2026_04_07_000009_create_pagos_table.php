<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rediseñada en 2026_04_21_000001_redesign_cuentas_cobrar_and_pagos.php
        if (Schema::hasTable('pagos')) return;

        Schema::create('pagos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cuenta_cobrar_id')
                ->constrained('cuentas_cobrar')
                ->restrictOnDelete();

            $table->decimal('monto_pago', 12, 2);
            $table->date('fecha_pago');

            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'cheque', 'tarjeta'])
                ->default('transferencia');

            $table->string('referencia', 100)->nullable();
            $table->string('comprobante', 255)->nullable();  // ruta del archivo adjunto

            // Quién registró el pago en el sistema
            $table->foreignId('registrado_por')
                ->constrained('users')
                ->restrictOnDelete();

            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
