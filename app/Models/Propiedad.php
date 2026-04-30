<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Propiedad extends Model
{
    protected $table = 'propiedades';

    protected $fillable = [
        'titulo',
        'tipo_propiedad',
        'precio_estimado',
        'negociable',
        'estado',
        'ciudad',
        'sector',
        'direccion',
        'metros_terreno',
        'metros_construccion',
        'numero_habitaciones',
        'numero_banos',
        'parqueaderos',
        'nombre_dueno',
        'telefono_dueno',
        'numero_propietarios',
        'captador_id',
        'descripcion',
        'cliente_id',
    ];

    protected $casts = [
        'negociable'          => 'boolean',
        'precio_estimado'     => 'decimal:2',
        'metros_terreno'      => 'decimal:2',
        'metros_construccion' => 'decimal:2',
    ];

    public function fotos()
    {
        return $this->hasMany(PropiedadFoto::class)->orderBy('orden');
    }

    public function portada()
    {
        return $this->hasOne(PropiedadFoto::class)->where('es_portada', true);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function captador()
    {
        return $this->belongsTo(User::class, 'captador_id');
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'disponible' => 'success',
            'en_proceso' => 'warning',
            'vendido'    => 'secondary',
            'cancelado'  => 'danger',
            default      => 'secondary',
        };
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'disponible' => 'Disponible',
            'en_proceso' => 'En Proceso',
            'vendido'    => 'Vendido',
            'cancelado'  => 'Cancelado',
            default      => ucfirst($this->estado),
        };
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo_propiedad) {
            'casa'           => 'Casa',
            'departamento'   => 'Departamento',
            'terreno'        => 'Terreno',
            'local_comercial'=> 'Local Comercial',
            'oficina'        => 'Oficina',
            default          => ucfirst($this->tipo_propiedad),
        };
    }

    public function getTieneHabitacionesAttribute(): bool
    {
        return in_array($this->tipo_propiedad, ['casa', 'departamento', 'oficina']);
    }

    public function getPrecioFormateadoAttribute(): string
    {
        return '$' . number_format($this->precio_estimado, 2, ',', '.');
    }
}
