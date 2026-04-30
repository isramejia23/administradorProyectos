<?php

namespace App\Console\Commands;

use App\Models\CuentaCobrar;
use Illuminate\Console\Command;

class RecalcularCuentasCobrar extends Command
{
    protected $signature   = 'cobros:recalcular {--id= : ID de una cuenta específica}';
    protected $description = 'Recalcula monto_base, monto_extras y monto_total de cuentas por cobrar';

    public function handle(): int
    {
        $query = CuentaCobrar::with(['trabajo.subtrabajos']);

        if ($this->option('id')) {
            $query->where('id', $this->option('id'));
        }

        $cuentas = $query->get();

        if ($cuentas->isEmpty()) {
            $this->warn('No se encontraron cuentas.');
            return self::FAILURE;
        }

        $this->info("Recalculando {$cuentas->count()} cuenta(s)...");
        $bar = $this->output->createProgressBar($cuentas->count());
        $bar->start();

        foreach ($cuentas as $cuenta) {
            $extras = (float) $cuenta->trabajo
                ->subtrabajos()
                ->where('es_principal', false)
                ->where('precio_incluido', false)
                ->sum('costo_especialista');

            // monto_base real = monto_total del trabajo MENOS los extras que el
            // controlador (con el bug) le había sumado incorrectamente
            $montoBase = max(0, (float) $cuenta->trabajo->monto_total - $extras);

            $montoPagado = (float) $cuenta->pagos()
                ->whereNull('anulado_at')
                ->sum('monto');

            // Restaurar Trabajo.monto_total al valor base original (sin extras)
            $cuenta->trabajo->updateQuietly(['monto_total' => $montoBase]);

            $cuenta->updateQuietly([
                'monto_base'   => $montoBase,
                'monto_extras' => $extras,
                'monto_total'  => $montoBase + $extras,
                'monto_pagado' => $montoPagado,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Recálculo completado.');

        return self::SUCCESS;
    }
}
