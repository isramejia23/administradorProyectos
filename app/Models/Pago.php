<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $fillable = [
        'cuenta_cobrar_id',
        'monto',
        'fecha_pago',
        'metodo_pago',
        'referencia',
        'registrado_por',
        'notas',
        'anulado_at',
        'motivo_anulacion',
    ];

    protected $casts = [
        'monto'       => 'decimal:2',
        'fecha_pago'  => 'date',
        'anulado_at'  => 'datetime',
    ];

    public function getIsAnuladoAttribute(): bool
    {
        return ! is_null($this->anulado_at);
    }

    // ── Relaciones ────────────────────────────────────────────

    public function cuentaCobrar()
    {
        return $this->belongsTo(CuentaCobrar::class, 'cuenta_cobrar_id');
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    // ── Labels ────────────────────────────────────────────────

    public static function metodosLabel(): array
    {
        return [
            'efectivo'      => 'Efectivo',
            'transferencia' => 'Transferencia',
            'cheque'        => 'Cheque',
            'tarjeta'       => 'Tarjeta',
        ];
    }

    public function getMetodoPagoLabelAttribute(): string
    {
        return self::metodosLabel()[$this->metodo_pago] ?? ucfirst($this->metodo_pago);
    }

    public function getMetodoPagoBadgeAttribute(): string
    {
        return match ($this->metodo_pago) {
            'efectivo'      => 'success',
            'transferencia' => 'primary',
            'cheque'        => 'secondary',
            'tarjeta'       => 'info',
            default         => 'secondary',
        };
    }
}
