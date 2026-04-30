<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $fillable = [
        'departamento_id',
        'nombre_servicio',
        'precio_sugerido',
        'estatus',
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }
}