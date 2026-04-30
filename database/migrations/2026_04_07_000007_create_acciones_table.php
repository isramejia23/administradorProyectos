<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subtrabajo_id')
                ->constrained('subtrabajos')
                ->cascadeOnDelete();

            // Usuario que registra o ejecuta la acción
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->text('descripcion');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->enum('estado', ['pendiente', 'proceso', 'terminado', 'cancelado'])
                ->default('pendiente');
            $table->text('observacion')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acciones');
    }
};
