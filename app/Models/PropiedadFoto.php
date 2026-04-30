<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropiedadFoto extends Model
{
    protected $fillable = [
        'propiedad_id',
        'ruta',
        'es_portada',
        'orden',
    ];

    protected $casts = [
        'es_portada' => 'boolean',
    ];

    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class);
    }
}
