<?php

namespace App\Observers;

use App\Models\Pago;

class PagoObserver
{
    public function created(Pago $pago): void
    {
        $this->sincronizar($pago);
    }

    public function updated(Pago $pago): void
    {
        $this->sincronizar($pago);
    }

    public function deleted(Pago $pago): void
    {
        $this->sincronizar($pago);
    }

    private function sincronizar(Pago $pago): void
    {
        $cuenta = $pago->cuentaCobrar;
        if (! $cuenta) return;

        $total = $cuenta->pagos()->whereNull('anulado_at')->sum('monto');

        $cuenta->updateQuietly(['monto_pagado' => $total]);
    }
}
