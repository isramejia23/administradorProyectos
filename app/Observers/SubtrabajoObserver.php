<?php

namespace App\Observers;

use App\Models\Historial;
use App\Models\Subtrabajo;
use App\Models\User;
use App\Models\CuentaCobrar;

class SubtrabajoObserver
{
    private const CAMPOS = [
        'estado',
        'responsable_id',
        'numero_tramite',
        'fecha_inicio',
        'fecha_estimada',
        'fecha_fin',
    ];

    // ── Sincronización de CuentaCobrar ────────────────────────

    public function created(Subtrabajo $subtrabajo): void
    {
        // Solo subtrabajos no incluidos y no principales afectan el monto extra
        if ($subtrabajo->es_principal || $subtrabajo->precio_incluido) return;

        $this->recalcularCuenta($subtrabajo);
    }

    public function updated(Subtrabajo $subtrabajo): void
    {
        // Recalcular si cambió precio_incluido o costo_especialista
        if ($subtrabajo->wasChanged(['precio_incluido', 'costo_especialista']) && ! $subtrabajo->es_principal) {
            $this->recalcularCuenta($subtrabajo);
        }
    }

    public function deleted(Subtrabajo $subtrabajo): void
    {
        if ($subtrabajo->es_principal || $subtrabajo->precio_incluido) return;

        $this->recalcularCuenta($subtrabajo);
    }

    private function recalcularCuenta(Subtrabajo $subtrabajo): void
    {
        $cuenta = CuentaCobrar::where('trabajo_id', $subtrabajo->trabajo_id)->first();
        if (! $cuenta) return;

        $cuenta->recalcularExtras();
    }

    // ── Historial de cambios ──────────────────────────────────

    public function updating(Subtrabajo $subtrabajo): void
    {
        // No registrar cambios del subtrabajo principal (oculto al usuario)
        if ($subtrabajo->es_principal) return;

        $userId = auth()->id();

        foreach (self::CAMPOS as $campo) {
            if (! $subtrabajo->isDirty($campo)) continue;

            $anterior = $subtrabajo->getOriginal($campo);
            $nuevo    = $subtrabajo->getAttribute($campo);

            Historial::create([
                'historiable_type' => Subtrabajo::class,
                'historiable_id'   => $subtrabajo->id,
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

        if ($campo === 'responsable_id') {
            $user = User::find($valor);
            return $user ? $user->nombre_completo : $valor;
        }

        if (in_array($campo, ['fecha_inicio', 'fecha_estimada', 'fecha_fin'])) {
            return \Carbon\Carbon::parse($valor)->format('d/m/Y');
        }

        return match($valor) {
            'pendiente'  => 'Pendiente',
            'proceso'    => 'En Proceso',
            'terminado'  => 'Terminado',
            'cancelado'  => 'Cancelado',
            default      => (string) $valor,
        };
    }
}
