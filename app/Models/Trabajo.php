<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CuentaCobrar;

class Trabajo extends Model
{
    protected $table = 'trabajos';

    protected $fillable = [
        'cliente_id',
        'servicio_id',
        'vendedor_id',
        'porcentaje_comision',
        'responsable_id',
        'departamento_id',
        'numero_tramite',
        'monto_total',
        'fecha_inicio',
        'fecha_estimada',
        'fecha_fin',
        'estado_trabajo',
        'motivo_rechazo',
        'razon',
        'resultado_esperado',
        'nivel_urgencia',
        'acuerdo_pagos',
        'trabajo_unico',
    ];

    protected $casts = [
        'fecha_inicio'         => 'datetime',
        'fecha_estimada'       => 'date',
        'fecha_fin'            => 'datetime',
        'trabajo_unico'        => 'boolean',
        'porcentaje_comision'  => 'decimal:2',
    ];

    public function getMontoComisionAttribute(): float
    {
        if (! $this->porcentaje_comision || ! $this->monto_total) {
            return 0;
        }
        return round($this->monto_total * $this->porcentaje_comision / 100, 2);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function subtrabajos()
    {
        return $this->hasMany(Subtrabajo::class);
    }

    public function historial()
    {
        return $this->morphMany(Historial::class, 'historiable')->latest('created_at');
    }

    public function cuentaCobrar()
    {
        return $this->hasOne(CuentaCobrar::class);
    }

    public function subtrabajoPrincipal()
    {
        return $this->hasOne(Subtrabajo::class)->where('es_principal', true);
    }

    public function crearSubtrabajoPrincipal(): Subtrabajo
    {
        return $this->subtrabajos()->create([
            'departamento_id'    => $this->departamento_id,
            'servicio_id'        => $this->servicio_id,
            'responsable_id'     => $this->responsable_id,
            'es_principal'       => true,
            'precio_incluido'    => true,
            'costo_especialista' => 0,
            'fecha_inicio'      => $this->fecha_inicio,
            'fecha_estimada'    => $this->fecha_estimada,
            'estado'            => 'pendiente',
        ]);
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match($this->estado_trabajo) {
            'solicitud'  => 'warning',
            'pendiente'  => 'info',
            'proceso'    => 'primary',
            'terminado'  => 'success',
            'cancelado'  => 'danger',
            'rechazado'  => 'danger',
            default      => 'secondary',
        };
    }

    public function getEstadoLabelAttribute(): string
    {
        return match($this->estado_trabajo) {
            'solicitud'  => 'Solicitud Pendiente',
            'proceso'    => 'En Proceso',
            'pendiente'  => 'Pendiente',
            'terminado'  => 'Terminado',
            'cancelado'  => 'Cancelado',
            'rechazado'  => 'Rechazado',
            default      => ucfirst($this->estado_trabajo),
        };
    }

    public function esSolicitud(): bool
    {
        return in_array($this->estado_trabajo, ['solicitud', 'rechazado']);
    }

    public function getUrgenciaBadgeAttribute(): string
    {
        return match($this->nivel_urgencia) {
            'alto'  => 'danger',
            'medio' => 'warning',
            'bajo'  => 'success',
            default => 'secondary',
        };
    }
}
