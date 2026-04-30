<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propiedades', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 200);
            $table->enum('tipo_propiedad', ['casa', 'departamento', 'terreno', 'local_comercial', 'oficina']);
            $table->decimal('precio_estimado', 12, 2);
            $table->boolean('negociable')->default(false);
            $table->enum('estado', ['disponible', 'en_proceso', 'vendido', 'cancelado'])->default('disponible');
            $table->string('ciudad', 100);
            $table->string('sector', 150)->nullable();
            $table->string('direccion', 250);
            $table->decimal('metros_terreno', 8, 2)->nullable();
            $table->decimal('metros_construccion', 8, 2)->nullable();
            $table->unsignedTinyInteger('numero_habitaciones')->nullable();
            $table->unsignedTinyInteger('numero_banos')->nullable();
            $table->unsignedTinyInteger('parqueaderos')->nullable();
            $table->string('nombre_dueno', 200);
            $table->string('telefono_dueno', 20);
            $table->text('descripcion')->nullable();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propiedades');
    }
};
