<?php

namespace App\Policies;

use App\Models\Trabajo;
use App\Models\User;

class TrabajoPolicy
{
    private function esAdmin(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Administrador']);
    }

    private function esJefeDeDepto(User $user): bool
    {
        return $user->hasRole('Jefe de Departamento');
    }

    private function esDesuDepto(User $user, Trabajo $trabajo): bool
    {
        return $trabajo->departamento_id === $user->departamento_id;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('ver-trabajo') || $user->can('ver-trabajo-departamento');
    }

    public function view(User $user, Trabajo $trabajo): bool
    {
        if ($this->esAdmin($user)) return true;
        if ($this->esJefeDeDepto($user) && $this->esDesuDepto($user, $trabajo)) return true;

        // El vendedor que creó la solicitud siempre puede verla
        if ($trabajo->vendedor_id === $user->id) return true;

        return $trabajo->responsable_id === $user->id
            || $user->can('ver-bandeja-trabajo');
    }

    /** Admite crear trabajo completo (crear-trabajo) o solicitud de vendedor (crear-solicitud-trabajo) */
    public function create(User $user): bool
    {
        return $user->can('crear-trabajo') || $user->can('crear-solicitud-trabajo');
    }

    public function delete(User $user, Trabajo $trabajo): bool
    {
        return $this->esAdmin($user) && $user->can('borrar-trabajo');
    }

    /** Solo admins y jefe de depto pueden editar el proyecto completo (nunca una solicitud) */
    public function update(User $user, Trabajo $trabajo): bool
    {
        if ($trabajo->esSolicitud()) return false;

        if ($this->esAdmin($user) && $user->can('editar-trabajo')) return true;
        if ($this->esJefeDeDepto($user) && $this->esDesuDepto($user, $trabajo) && $user->can('editar-trabajo')) return true;

        return false;
    }

    /** Gestionar: responsable del proyecto o jefe de su depto. Bloqueado para solicitudes. */
    public function gestionar(User $user, Trabajo $trabajo): bool
    {
        if ($trabajo->esSolicitud()) return false;

        if ($this->esAdmin($user)) return true;
        if ($this->esJefeDeDepto($user) && $this->esDesuDepto($user, $trabajo)) return true;

        return $user->can('gestionar-trabajo')
            && $trabajo->responsable_id === $user->id;
    }

    /** Aceptar: sin responsable + mismo departamento. Bloqueado para solicitudes. */
    public function aceptar(User $user, Trabajo $trabajo): bool
    {
        if ($trabajo->esSolicitud()) return false;

        return $user->can('editar-trabajo')
            && is_null($trabajo->responsable_id)
            && $trabajo->departamento_id === $user->departamento_id;
    }

    /** Asignar responsable: admin/jefe en su depto. Bloqueado para solicitudes. */
    public function asignarResponsable(User $user, Trabajo $trabajo): bool
    {
        if ($trabajo->esSolicitud()) return false;
        if (! $user->can('asignar-responsable')) return false;
        if ($this->esAdmin($user)) return true;
        if ($this->esJefeDeDepto($user)) return $this->esDesuDepto($user, $trabajo);

        return is_null($trabajo->responsable_id);
    }

    /** Aprobar o rechazar una solicitud de vendedor */
    public function aprobar(User $user, Trabajo $trabajo): bool
    {
        if (! $user->can('aprobar-trabajo')) return false;
        if ($trabajo->estado_trabajo !== 'solicitud') return false;

        // Admin ve todo; Jefe solo las de su departamento, pero nunca las suyas propias
        if ($this->esAdmin($user)) return true;
        if ($this->esJefeDeDepto($user)) {
            $esCreador = $trabajo->vendedor_id === $user->id || $trabajo->responsable_id === $user->id;
            return ! $esCreador && $this->esDesuDepto($user, $trabajo);
        }

        return false;
    }
}
