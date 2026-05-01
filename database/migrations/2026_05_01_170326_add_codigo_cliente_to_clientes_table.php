<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('codigo_cliente', 20)->nullable()->unique()->after('id');
        });

        // Asignar códigos a clientes existentes empezando desde CP-1420
        $clientes = DB::table('clientes')->orderBy('id')->get();
        $numero = 1420;
        foreach ($clientes as $cliente) {
            DB::table('clientes')
                ->where('id', $cliente->id)
                ->update(['codigo_cliente' => 'CP-' . $numero]);
            $numero++;
        }
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique(['codigo_cliente']);
            $table->dropColumn('codigo_cliente');
        });
    }
};
