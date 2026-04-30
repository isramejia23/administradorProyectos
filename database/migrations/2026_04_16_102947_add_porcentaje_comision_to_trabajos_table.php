<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trabajos', function (Blueprint $table) {
            // Porcentaje de comisión asignado por el administrador al vendedor del proyecto
            $table->decimal('porcentaje_comision', 5, 2)->nullable()->default(null)->after('vendedor_id');
        });
    }

    public function down(): void
    {
        Schema::table('trabajos', function (Blueprint $table) {
            $table->dropColumn('porcentaje_comision');
        });
    }
};
