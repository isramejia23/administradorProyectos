<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->timestamp('anulado_at')->nullable()->after('notas');
            $table->text('motivo_anulacion')->nullable()->after('anulado_at');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn(['anulado_at', 'motivo_anulacion']);
        });
    }
};
