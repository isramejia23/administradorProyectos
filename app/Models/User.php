<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $fillable = [
        'departamento_id',
        'nombre',
        'apellido',
        'email',
        'password',
        'cedula',
        'telefono',
        'direccion',
        'estado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function trabajosResponsable()
    {
        return $this->hasMany(Trabajo::class, 'responsable_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellido}");
    }

    /**
     * Indica si el usuario tiene visibilidad global (todos los departamentos).
     * Centraliza la regla en un solo lugar: si cambia el criterio, solo se edita aquí.
     */
    public function veeTodosLosDepartamentos(): bool
    {
        return $this->hasRole(['Super Admin', 'Administrador']);
    }
}
