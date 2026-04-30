<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trabajos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->restrictOnDelete();

            $table->foreignId('servicio_id')
                ->constrained('servicios')
                ->restrictOnDelete();

            // Rol dual: vendedor y responsable pueden ser el mismo usuario o distintos
            $table->foreignId('vendedor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('responsable_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Departamento principal que lidera el trabajo
            $table->foreignId('departamento_id')
                ->constrained('departamentos')
                ->restrictOnDelete();

            $table->string('numero_tramite', 20)->nullable();
            $table->decimal('monto_total', 12, 2);
            $table->datetime('fecha_inicio')->nullable();
            $table->date('fecha_estimada')->nullable();
            $table->datetime('fecha_fin')->nullable();

            $table->enum('estado_trabajo', ['pendiente', 'proceso', 'terminado', 'cancelado'])
                ->default('pendiente');

            $table->text('razon')->nullable();
            $table->string('resultado_esperado', 256)->nullable();
            $table->enum('nivel_urgencia', ['bajo', 'medio', 'alto'])->default('medio');
            $table->string('acuerdo_pagos', 256)->nullable();

            // Si es true, el trabajo no tiene subtrabajos manuales;
            // se crea uno principal automáticamente para registrar acciones directas
            $table->boolean('trabajo_unico')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trabajos');
    }
};
