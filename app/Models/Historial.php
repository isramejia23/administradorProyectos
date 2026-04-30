<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Historial extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'historiable_type',
        'historiable_id',
        'user_id',
        'campo',
        'valor_anterior',
        'valor_nuevo',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function historiable()
    {
        return $this->morphTo();
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Etiquetas legibles por campo
    public static function labelCampo(string $campo): string
    {
        return match($campo) {
            'estado_trabajo'  => 'Estado',
            'estado'          => 'Estado',
            'responsable_id'  => 'Responsable',
            'vendedor_id'     => 'Vendedor',
            'trabajo_unico'   => 'Tipo de Trabajo',
            'nivel_urgencia'  => 'Nivel de Urgencia',
            'monto_total'     => 'Monto Total',
            'numero_tramite'  => 'N° Trámite',
            'fecha_inicio'    => 'Fecha de Inicio',
            'fecha_estimada'  => 'Fecha Estimada',
            'fecha_fin'       => 'Fecha de Finalización',
            default           => ucfirst(str_replace('_', ' ', $campo)),
        };
    }
}
