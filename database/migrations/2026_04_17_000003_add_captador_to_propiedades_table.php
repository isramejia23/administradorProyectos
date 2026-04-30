<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->unsignedTinyInteger('numero_propietarios')->default(1)->after('telefono_dueno');
            $table->foreignId('captador_id')->nullable()->constrained('users')->nullOnDelete()->after('numero_propietarios');
        });
    }

    public function down(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->dropForeign(['captador_id']);
            $table->dropColumn(['numero_propietarios', 'captador_id']);
        });
    }
};
