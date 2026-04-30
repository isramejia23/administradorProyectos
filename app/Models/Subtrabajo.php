<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subtrabajo extends Model
{
    protected $fillable = [
        'trabajo_id',
        'departamento_id',
        'servicio_id',
        'responsable_id',
        'numero_tramite',
        'costo_especialista',
        'precio_incluido',
        'es_principal',
        'fecha_inicio',
        'fecha_estimada',
        'fecha_fin',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inicio'   => 'datetime',
        'fecha_estimada' => 'date',
        'fecha_fin'      => 'datetime',
        'precio_incluido' => 'boolean',
        'es_principal'    => 'boolean',
    ];

    public function trabajo()      { return $this->belongsTo(Trabajo::class); }
    public function departamento() { return $this->belongsTo(Departamento::class); }
    public function servicio()     { return $this->belongsTo(Servicio::class); }
    public function responsable()  { return $this->belongsTo(User::class, 'responsable_id'); }
    public function acciones()     { return $this->hasMany(Accion::class); }
    public function historial()    { return $this->morphMany(Historial::class, 'historiable')->latest('created_at'); }

    public function getEstadoBadgeAttribute(): string
    {
        return match($this->estado) {
            'proceso'   => 'primary',
            'terminado' => 'success',
            'cancelado' => 'secondary',
            default     => 'info',
        };
    }

    public function getEstadoLabelAttribute(): string
    {
        return match($this->estado) {
            'proceso'   => 'En Proceso',
            'pendiente' => 'Pendiente',
            'terminado' => 'Terminado',
            'cancelado' => 'Cancelado',
            default     => ucfirst($this->estado),
        };
    }
}