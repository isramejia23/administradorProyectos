<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subtrabajos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trabajo_id')
                ->constrained('trabajos')
                ->cascadeOnDelete();

            // Define la "bandeja de entrada" del subtrabajo
            $table->foreignId('departamento_id')
                ->constrained('departamentos')
                ->restrictOnDelete();

            $table->foreignId('servicio_id')
                ->nullable()
                ->constrained('servicios')
                ->nullOnDelete();

            // El especialista que ejecuta este hito (nullable: puede quedar sin asignar)
            $table->foreignId('responsable_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Lo que se le paga al especialista por este subtrabajo
            $table->string('numero_tramite', 20)->nullable();
            $table->decimal('costo_especialista', 12, 2)->default(0);

            // Indica si el costo ya está incluido en el monto del proyecto
            $table->boolean('precio_incluido')->default(false);

            // Subtrabajo creado automáticamente para trabajos únicos (sin subtrabajos manuales)
            $table->boolean('es_principal')->default(false);

            $table->datetime('fecha_inicio')->nullable();
            $table->date('fecha_estimada')->nullable();
            $table->datetime('fecha_fin')->nullable();

            $table->enum('estado', ['pendiente', 'proceso', 'terminado', 'cancelado'])
                ->default('pendiente');

            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subtrabajos');
    }
};
