<?php

namespace App\Policies;

use App\Models\Accion;
use App\Models\Subtrabajo;
use App\Models\User;

class AccionPolicy
{
    private function esAdmin(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Administrador']);
    }

    /** Crear nueva acción: admin o responsable del subtrabajo */
    public function create(User $user, Subtrabajo $subtrabajo): bool
    {
        if ($this->esAdmin($user)) return true;

        return $user->can('crear-accion')
            && $subtrabajo->responsable_id === $user->id;
    }

    /** Editar fecha_fin/observacion y cambiar estado:
     *  - Si está terminada: solo admin
     *  - Si no: admin o responsable del subtrabajo */
    public function update(User $user, Accion $accion): bool
    {
        if ($this->esAdmin($user)) return true;

        if (in_array($accion->estado, ['terminado', 'cancelado'])) return false;

        return $user->can('editar-accion')
            && $accion->subtrabajo->responsable_id === $user->id;
    }
}
