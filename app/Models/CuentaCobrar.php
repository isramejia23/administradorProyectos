<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuentaCobrar extends Model
{
    protected $table = 'cuentas_cobrar';

    protected $fillable = [
        'trabajo_id',
        'monto_base',
        'monto_extras',
        'monto_total',
        'monto_pagado',
        'fecha_vencimiento',
        'notas',
    ];

    protected $casts = [
        'monto_base'       => 'decimal:2',
        'monto_extras'     => 'decimal:2',
        'monto_total'      => 'decimal:2',
        'monto_pagado'     => 'decimal:2',
        'fecha_vencimiento'=> 'date',
    ];

    // ── Relaciones ────────────────────────────────────────────

    public function trabajo()
    {
        return $this->belongsTo(Trabajo::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class)->latest();
    }

    // ── Accessors calculados (no columnas en DB) ──────────────

    public function getSaldoAttribute(): float
    {
        return max(0, (float) $this->monto_total - (float) $this->monto_pagado);
    }

    public function getEstadoPagoAttribute(): string
    {
        if ((float) $this->monto_pagado <= 0)                              return 'pendiente';
        if ((float) $this->monto_pagado >= (float) $this->monto_total)    return 'pagado';
        return 'parcial';
    }

    public function getEstadoPagoBadgeAttribute(): string
    {
        return match ($this->estado_pago) {
            'pagado'    => 'success',
            'parcial'   => 'naranja',
            'pendiente' => 'info',
            default     => 'secondary',
        };
    }

    public function getEstadoPagoLabelAttribute(): string
    {
        return match ($this->estado_pago) {
            'pagado'    => 'Pagado',
            'parcial'   => 'Parcial',
            'pendiente' => 'Pendiente',
            default     => 'Desconocido',
        };
    }

    // ── Porcentaje pagado para barra de progreso ──────────────

    public function getPorcentajePagadoAttribute(): float
    {
        if ((float) $this->monto_total <= 0) return 0;
        return min(100, round(((float) $this->monto_pagado / (float) $this->monto_total) * 100, 1));
    }

    // ── Distribución proporcional por componente ──────────────
    // Devuelve array de componentes con su monto, cubierto y pendiente.
    // Componentes: gestión del trabajo base + cada subtrabajo.

    public function distribucion(): array
    {
        $trabajo     = $this->trabajo->load(['subtrabajos' => fn($q) => $q->with('servicio')->where('es_principal', false)]);
        $subtrabajos = $trabajo->subtrabajos;

        $totalIncluidos = (float) $subtrabajos->where('precio_incluido', true)->sum('costo_especialista');
        $montoGestion   = max(0, (float) $this->monto_base - $totalIncluidos);

        // El pago cubre primero la parte base (gestión + incluidos),
        // y solo lo que sobra va a los extras.
        $pagadoBase   = min((float) $this->monto_pagado, (float) $this->monto_base);
        $pagadoExtras = max(0, (float) $this->monto_pagado - (float) $this->monto_base);

        $componentes = [];

        // Gestión del trabajo principal
        if ($montoGestion > 0 || $subtrabajos->isEmpty()) {
            $componentes[] = $this->componenteItem(
                'Gestión del Proyecto', 'bi-kanban', '#0d6efd',
                $montoGestion ?: (float) $this->monto_base,
                (float) $this->monto_base,
                $pagadoBase
            );
        }

        // Subtrabajos incluidos (se cubren con el pago base)
        foreach ($subtrabajos->where('precio_incluido', true) as $sub) {
            $componentes[] = $this->componenteItem(
                $sub->servicio?->nombre_servicio ?? "Sub #{$sub->id}",
                'bi-diagram-3', '#6f42c1',
                (float) $sub->costo_especialista,
                (float) $this->monto_base,
                $pagadoBase
            );
        }

        // Subtrabajos NO incluidos (extras — se cubren solo con lo que sobra del pago base)
        $totalExtras = max((float) $this->monto_extras, 0.01);
        foreach ($subtrabajos->where('precio_incluido', false) as $sub) {
            $componentes[] = $this->componenteItem(
                ($sub->servicio?->nombre_servicio ?? "Sub #{$sub->id}") . ' (extra)',
                'bi-diagram-3', '#fd7e14',
                (float) $sub->costo_especialista,
                $totalExtras,
                $pagadoExtras
            );
        }

        return $componentes;
    }

    private function componenteItem(string $nombre, string $icono, string $color, float $monto, float $totalSector, float $pagadoSector): array
    {
        $proporcion = $totalSector > 0 ? $monto / $totalSector : 0;
        $cubierto   = min($monto, $pagadoSector * $proporcion);
        return [
            'nombre'     => $nombre,
            'icono'      => $icono,
            'color'      => $color,
            'monto'      => $monto,
            'cubierto'   => round($cubierto, 2),
            'pendiente'  => round(max(0, $monto - $cubierto), 2),
            'porcentaje' => $monto > 0 ? round(($cubierto / $monto) * 100, 1) : 0,
        ];
    }

    // ── Recalcular monto_extras desde subtrabajos actuales ────

    public function recalcularExtras(): void
    {
        $extras = $this->trabajo
            ->subtrabajos()
            ->where('es_principal', false)
            ->where('precio_incluido', false)
            ->sum('costo_especialista');

        $this->update([
            'monto_extras' => $extras,
            'monto_total'  => (float) $this->monto_base + $extras,
        ]);
    }
}
