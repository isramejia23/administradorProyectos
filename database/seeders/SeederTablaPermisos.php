<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class SeederTablaPermisos extends Seeder
{
    public function run(): void
    {
        $permisos = [
            // Roles
            'ver-rol',
            'crear-rol',
            'editar-rol',
            'borrar-rol',

            // Usuarios
            'ver-usuario',
            'crear-usuario',
            'editar-usuario',
            'borrar-usuario',

            // Departamentos
            'ver-departamento',
            'crear-departamento',
            'editar-departamento',
            'borrar-departamento',

            // Clientes
            'ver-cliente',
            'crear-cliente',
            'editar-cliente',
            'borrar-cliente',

            // Servicios
            'ver-servicio',
            'crear-servicio',
            'editar-servicio',
            'borrar-servicio',

            // Trabajos
            'ver-trabajo',                   // lista completa (admin)
            'ver-trabajo-departamento',      // lista filtrada al propio departamento (jefe de depto)
            'ver-bandeja-trabajo',           // bandeja: proyectos sin responsable del propio dpto
            'ver-mis-trabajos',              // mis proyectos asignados
            'crear-trabajo',
            'editar-trabajo',
            'gestionar-trabajo',             // responsable: actualiza estado, fecha_fin, razon, resultado
            'asignar-responsable',           // admin/jefe: asigna responsable a un proyecto
            'borrar-trabajo',

            // Subtrabajos
            'ver-subtrabajo',
            'crear-subtrabajo',
            'editar-subtrabajo',
            'borrar-subtrabajo',
            'asignar-responsable-subtrabajo', // admin/director: asigna responsable a subtrabajo sin dueño

            // Historial
            'ver-historial-trabajo',     // ver historial de cambios de un proyecto
            'ver-historial-subtrabajo',  // ver historial de cambios de un subtrabajo

            // Acciones
            'ver-accion',
            'crear-accion',
            'editar-accion',  // cambiar estado, fecha_fin, observacion (bloqueado si terminado)
            'borrar-accion',

            // Cuentas por cobrar
            'ver-cuenta-cobrar',    // ver el estado de cuenta de un proyecto
            'editar-cuenta-cobrar', // editar fecha de vencimiento y notas
            'anular-cuenta-cobrar', // solo Super Admin: anular/resetear cuenta

            // Pagos
            'ver-pago',     // ver listado de pagos
            'crear-pago',   // registrar un nuevo pago
            'editar-pago',  // corregir un pago existente
            'borrar-pago',  // anular/eliminar un pago

            // Ventas
            'ver-mis-ventas',           // vendedor/profesional: ve sus proyectos donde figura como vendedor
            'ver-ventas-admin',         // admin: ve todos los proyectos con sus vendedores y comisiones
            'asignar-vendedor',         // admin: asigna o cambia el vendedor de un proyecto
            'editar-comision',          // admin: asigna/edita el porcentaje de comisión de un proyecto
            'ver-home-vendedor',        // vendedor: accede al dashboard exclusivo de ventas y comisiones
            'crear-solicitud-trabajo',  // vendedor: crea una solicitud de proyecto pendiente de aprobación
            'ver-solicitudes-trabajo',  // admin/jefe: ve la bandeja de solicitudes pendientes de aprobación
            'aprobar-trabajo',          // admin/jefe: aprueba o rechaza solicitudes de vendedores

            // Reportes
            'ver-reporte',

            // Propiedades
            'ver-propiedad',
            'crear-propiedad',
            'editar-propiedad',
            'borrar-propiedad',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }
    }
}
