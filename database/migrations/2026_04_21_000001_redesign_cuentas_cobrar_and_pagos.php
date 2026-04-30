<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar ambas tablas en orden correcto (FK: pagos → cuentas_cobrar)
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('cuentas_cobrar');

        // ── cuentas_cobrar (nueva estructura) ─────────────────
        Schema::create('cuentas_cobrar', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trabajo_id')
                ->unique()
                ->constrained('trabajos')
                ->cascadeOnDelete();

            // monto_base = Trabajo.monto_total al momento de crear
            $table->decimal('monto_base',   12, 2)->default(0);
            // monto_extras = SUM(subtrabajo.costo_especialista WHERE precio_incluido=false)
            $table->decimal('monto_extras', 12, 2)->default(0);
            // monto_total = monto_base + monto_extras (guardado para consistencia)
            $table->decimal('monto_total',  12, 2)->default(0);
            // monto_pagado se actualiza via observer con cada pago
            $table->decimal('monto_pagado', 12, 2)->default(0);

            $table->date('fecha_vencimiento')->nullable();
            $table->text('notas')->nullable();

            $table->timestamps();
        });

        // ── pagos (nueva estructura) ───────────────────────────
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cuenta_cobrar_id')
                ->constrained('cuentas_cobrar')
                ->cascadeOnDelete();

            $table->decimal('monto', 12, 2);
            $table->date('fecha_pago');
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'cheque', 'tarjeta'])
                ->default('transferencia');
            $table->string('referencia', 100)->nullable();

            $table->foreignId('registrado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notas')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('cuentas_cobrar');
    }
};
