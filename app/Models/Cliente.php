<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Trabajo;
use App\Models\CuentaCobrar;

class Cliente extends Model
{
    protected $fillable = [
        'codigo_cliente',
        'nombres_clientes',
        'apellidos_clientes',
        'razon_social',
        'identificacion_clientes',
        'email_cliente',
        'celular_clientes',
        'estado',
        'claves_observaciones',
    ];

    protected static function booted(): void
    {
        static::creating(function (Cliente $cliente) {
            if (empty($cliente->codigo_cliente)) {
                $ultimo = static::whereNotNull('codigo_cliente')
                    ->orderByRaw("CAST(SUBSTRING(codigo_cliente, 4) AS UNSIGNED) DESC")
                    ->value('codigo_cliente');

                $numero = $ultimo ? ((int) substr($ultimo, 3)) + 1 : 1420;
                $cliente->codigo_cliente = 'CP-' . $numero;
            }
        });
    }

    /**
     * Devuelve el nombre completo si existe, o la razón social como fallback.
     */
    public function getNombreCompletoAttribute(): string
    {
        $nombre = trim($this->nombres_clientes . ' ' . $this->apellidos_clientes);
        return $nombre ?: ($this->razon_social ?? '—');
    }

    public function trabajos()
    {
        return $this->hasMany(Trabajo::class);
    }

    public function cuentasCobrar()
    {
        return $this->hasManyThrough(CuentaCobrar::class, Trabajo::class);
    }
}
