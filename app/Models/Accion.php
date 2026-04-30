<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accion extends Model
{
    protected $table = 'acciones';

    protected $fillable = [
        'subtrabajo_id',
        'user_id',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'observacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    public function subtrabajo() { return $this->belongsTo(Subtrabajo::class); }
    public function usuario()    { return $this->belongsTo(User::class, 'user_id'); }

    public function getEstadoBadgeAttribute(): string
    {
        return match($this->estado) {
            'proceso'   => 'primary',
            'terminado' => 'success',
            'cancelado' => 'secondary',
            default     => 'naranja',
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