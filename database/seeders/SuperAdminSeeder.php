<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // ── Super Admin: todos los permisos ───────────────────
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->syncPermissions(Permission::all());

        // ── Administrador: gestión completa de proyectos ──────
        $admin = Role::firstOrCreate(['name' => 'Administrador']);
        $admin->syncPermissions(Permission::whereIn('name', [
            'ver-trabajo', 'ver-bandeja-trabajo', 'ver-mis-trabajos',
            'crear-trabajo', 'editar-trabajo', 'asignar-responsable',
            'ver-subtrabajo', 'crear-subtrabajo', 'editar-subtrabajo', 'asignar-responsable-subtrabajo',
            'ver-accion', 'crear-accion', 'editar-accion',
            'ver-historial-trabajo', 'ver-historial-subtrabajo',
            'ver-cliente', 'crear-cliente', 'editar-cliente',
            'ver-servicio',
            'ver-departamento',
            'ver-usuario', 'crear-usuario', 'editar-usuario',
            'ver-reporte',
            // Ventas
            'ver-ventas-admin', 'asignar-vendedor', 'editar-comision',
            // Solicitudes de vendedores
            'ver-solicitudes-trabajo', 'aprobar-trabajo',
            // Propiedades
            'ver-propiedad', 'crear-propiedad', 'editar-propiedad',
            // Cobros
            'ver-cuenta-cobrar', 'editar-cuenta-cobrar',
            'ver-pago', 'crear-pago', 'editar-pago', 'borrar-pago',
        ])->get());

        // ── Jefe de Departamento ──────────────────────────────
        $jefe = Role::firstOrCreate(['name' => 'Jefe de Departamento']);
        $jefe->syncPermissions(Permission::whereIn('name', [
            // Proyectos — ve solo su departamento
            'ver-trabajo-departamento', 'ver-bandeja-trabajo', 'ver-mis-trabajos',
            'crear-trabajo', 'editar-trabajo', 'gestionar-trabajo',
            'asignar-responsable',
            // Subproyectos
            'ver-subtrabajo', 'crear-subtrabajo', 'editar-subtrabajo',
            'asignar-responsable-subtrabajo',
            // Acciones
            'ver-accion', 'crear-accion', 'editar-accion',
            // Historial
            'ver-historial-trabajo', 'ver-historial-subtrabajo',
            // Clientes
            'ver-cliente', 'crear-cliente', 'editar-cliente',
            // Servicios y departamentos
            'ver-servicio', 'ver-departamento',
            // Reportes
            'ver-reporte',
            // Ventas (puede figurar como vendedor)
            'ver-mis-ventas',
            // Solicitudes de vendedores
            'ver-solicitudes-trabajo', 'aprobar-trabajo',
            // Propiedades
            'ver-propiedad',

        ])->get());

        // ── Profesional (Abogado, Contador, etc.) ─────────────
        $profesional = Role::firstOrCreate(['name' => 'Profesional']);
        $profesional->syncPermissions(Permission::whereIn('name', [
            'ver-bandeja-trabajo', 'ver-mis-trabajos',
            'crear-trabajo', 'editar-trabajo', 'gestionar-trabajo',
            'crear-subtrabajo', 'editar-subtrabajo',
            'ver-accion', 'crear-accion', 'editar-accion',
            'ver-historial-trabajo', 'ver-historial-subtrabajo',
            'ver-cliente', 'crear-cliente', 'editar-cliente',
            'ver-servicio',
            // Ventas: el profesional puede comisionar en proyectos como vendedor
            'ver-mis-ventas',
            // Propiedades
            'ver-propiedad',
        ])->get());

        // ── Facturación / Cobros ──────────────────────────────
        $facturacion = Role::firstOrCreate(['name' => 'Facturación']);
        $facturacion->syncPermissions(Permission::whereIn('name', [
            'ver-trabajo',          // ver proyectos para identificar a cuál aplica el pago
            'ver-cliente', 'crear-cliente',
            'ver-cuenta-cobrar',    // ver cuentas por cobrar
            'editar-cuenta-cobrar', // editar fecha vencimiento y notas
            'ver-pago',             // ver pagos
            'crear-pago',           // registrar pagos
            'editar-pago',          // corregir pagos
            'borrar-pago',          // anular pagos
        ])->get());

        // ── Jefe de Ventas: igual que Vendedor pero ve las ventas de todo el equipo ──
        $jefeVentas = Role::firstOrCreate(['name' => 'Jefe de Ventas']);
        $jefeVentas->syncPermissions(Permission::whereIn('name', [
            'ver-mis-ventas',
            'ver-ventas-admin',         // diferencia clave: ve todos los vendedores y comisiones
            'ver-home-vendedor',
            'crear-solicitud-trabajo',
            'ver-cliente', 'crear-cliente',
            'ver-servicio',
            // Propiedades
            'ver-propiedad',
        ])->get());

        // ── Vendedor: acceso exclusivo a sus ventas + crear solicitudes ──
        $vendedor = Role::firstOrCreate(['name' => 'Vendedor']);
        $vendedor->syncPermissions(Permission::whereIn('name', [
            'ver-mis-ventas',
            'ver-home-vendedor',
            'crear-solicitud-trabajo',  // puede crear solicitudes de proyecto
            'ver-cliente', 'crear-cliente',
            'ver-servicio',
            // Propiedades
            'ver-propiedad',
        ])->get());

        // ── Usuario Super Admin ────────────────────────────────
        $user = User::firstOrCreate(
            ['email' => 'admin@grupopiguma.com'],
            [
                'nombre'    => 'Super',
                'apellido'  => 'Admin',
                'cedula'    => '0000000000',
                'telefono'  => '0000000000',
                'direccion' => 'Oficina Principal',
                'estado'    => 'Activo',
                'password'  => bcrypt('Admin2024*'),
            ]
        );

        $user->syncRoles([$superAdmin]);
    }
}
