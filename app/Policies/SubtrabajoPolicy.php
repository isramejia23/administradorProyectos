<?php

namespace App\Policies;

use App\Models\Subtrabajo;
use App\Models\Trabajo;
use App\Models\User;

class SubtrabajoPolicy
{
    private function esAdmin(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Administrador']);
    }

    private function esJefeDeDepto(User $user): bool
    {
        return $user->hasRole('Jefe de Departamento');
    }

    private function esDesuDepto(User $user, Subtrabajo $subtrabajo): bool
    {
        return $subtrabajo->departamento_id === $user->departamento_id;
    }

    public function create(User $user, ?Trabajo $proyecto = null): bool
    {
        if ($this->esAdmin($user)) return true;
        if (!$user->can('crear-subtrabajo')) return false;

        if ($proyecto) {
            if ($this->esJefeDeDepto($user) && $proyecto->departamento_id === $user->departamento_id) return true;
            return $proyecto->responsable_id === $user->id;
        }

        return false;
    }

    /** Editar subtrabajo: admin, jefe de su depto o responsable asignado */
    public function update(User $user, Subtrabajo $subtrabajo): bool
    {
        if ($this->esAdmin($user)) return true;
        if ($this->esJefeDeDepto($user) && $this->esDesuDepto($user, $subtrabajo) && $user->can('editar-subtrabajo')) return true;

        return $user->can('editar-subtrabajo')
            && $subtrabajo->responsable_id === $user->id;
    }

    public function delete(User $user, Subtrabajo $subtrabajo): bool
    {
        return $this->esAdmin($user) && $user->can('borrar-subtrabajo');
    }

    /** Aceptar: sin responsable + usuario del mismo departamento */
    public function aceptar(User $user, Subtrabajo $subtrabajo): bool
    {
        return $user->can('ver-bandeja-trabajo')
            && is_null($subtrabajo->responsable_id)
            && $subtrabajo->departamento_id === $user->departamento_id;
    }

    /** Asignar responsable: admin, jefe de depto (en su depto), o responsable del proyecto padre */
    public function asignarResponsable(User $user, Subtrabajo $subtrabajo): bool
    {
        if (!$user->can('asignar-responsable-subtrabajo')) {
            return $subtrabajo->trabajo->responsable_id === $user->id
                && is_null($subtrabajo->responsable_id);
        }

        if ($this->esAdmin($user)) return true;
        if ($this->esJefeDeDepto($user)) return $this->esDesuDepto($user, $subtrabajo);

        return is_null($subtrabajo->responsable_id);
    }

    /** Finalizar: admin, jefe de su depto, o responsable del subtrabajo */
    public function finalizar(User $user, Subtrabajo $subtrabajo): bool
    {
        if (in_array($subtrabajo->estado, ['terminado', 'cancelado'])) return false;
        if ($this->esAdmin($user)) return true;
        if ($this->esJefeDeDepto($user) && $this->esDesuDepto($user, $subtrabajo)) return true;

        return $subtrabajo->responsable_id === $user->id;
    }
}