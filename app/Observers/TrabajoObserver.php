<?php

namespace App\Observers;

use App\Models\CuentaCobrar;
use App\Models\Historial;
use App\Models\Trabajo;
use App\Models\User;

class TrabajoObserver
{
    private const CAMPOS = [
        'estado_trabajo',
        'responsable_id',
        'vendedor_id',
        'trabajo_unico',
        'nivel_urgencia',
        'monto_total',
        'fecha_inicio',
        'fecha_estimada',
        'fecha_fin',
    ];

    public function created(Trabajo $trabajo): void
    {
        // Al crear un trabajo nuevo, la cuenta no tiene subtrabajos aún
        // monto_extras se recalculará cuando se agreguen subtrabajos no incluidos
        CuentaCobrar::create([
            'trabajo_id'   => $trabajo->id,
            'monto_base'   => $trabajo->monto_total,
            'monto_extras' => 0,
            'monto_total'  => $trabajo->monto_total,
            'monto_pagado' => 0,
        ]);
    }

    public function updated(Trabajo $trabajo): void
    {
        if (! $trabajo->wasChanged('monto_total')) return;

        $cuenta = $trabajo->cuentaCobrar;
        if (! $cuenta) return;

        $cuenta->update([
            'monto_base'  => $trabajo->monto_total,
            'monto_total' => $trabajo->monto_total + $cuenta->monto_extras,
        ]);
    }

    public function updating(Trabajo $trabajo): void
    {
        $userId = auth()->id();

        foreach (self::CAMPOS as $campo) {
            if (! $trabajo->isDirty($campo)) continue;

            $anterior = $trabajo->getOriginal($campo);
            $nuevo    = $trabajo->getAttribute($campo);

            Historial::create([
                'historiable_type' => Trabajo::class,
                'historiable_id'   => $trabajo->id,
                'user_id'          => $userId,
                'campo'            => $campo,
                'valor_anterior'   => $this->formatear($campo, $anterior),
                'valor_nuevo'      => $this->formatear($campo, $nuevo),
            ]);
        }
    }

    private function formatear(string $campo, mixed $valor): ?string
    {
        if (is_null($valor)) return null;

        if (in_array($campo, ['responsable_id', 'vendedor_id'])) {
            $user = User::find($valor);
            return $user ? $user->nombre_completo : (string) $valor;
        }

        if (in_array($campo, ['fecha_inicio', 'fecha_estimada', 'fecha_fin'])) {
            return \Carbon\Carbon::parse($valor)->format('d/m/Y');
        }

        if ($campo === 'trabajo_unico') {
            return $valor ? 'Trabajo Único' : 'Múltiples Trabajos';
        }

        if ($campo === 'monto_total') {
            return '$' . number_format((float) $valor, 2);
        }

        return match($valor) {
            'pendiente'  => 'Pendiente',
            'proceso'    => 'En Proceso',
            'terminado'  => 'Terminado',
            'cancelado'  => 'Cancelado',
            'bajo'       => 'Bajo',
            'medio'      => 'Medio',
            'alto'       => 'Alto',
            default      => (string) $valor,
        };
    }
}
