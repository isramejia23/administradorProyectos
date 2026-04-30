<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombres_clientes', 150);
            $table->string('apellidos_clientes', 150);
            $table->string('razon_social', 200)->nullable();
            $table->string('identificacion_clientes', 20)->unique();
            $table->string('email_cliente', 150)->nullable();
            $table->string('celular_clientes', 15)->nullable();
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
