<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departamento_id')
                ->constrained('departamentos')
                ->restrictOnDelete();
            $table->string('nombre_servicio', 150);
            $table->decimal('precio_sugerido', 12, 2)->default(0);
            $table->enum('estatus', ['Activo', 'Inactivo'])->default('Activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
