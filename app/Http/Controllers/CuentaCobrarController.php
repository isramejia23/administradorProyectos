<?php

namespace App\Http\Controllers;

use App\Models\CuentaCobrar;
use App\Models\Trabajo;
use App\Models\Cliente;
use Illuminate\Http\Request;

class CuentaCobrarController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('ver-cuenta-cobrar'), 403);

        $applyFilters = function ($q) use ($request) {
            if ($request->filled('estado_pago')) {
                $q->where(function ($inner) use ($request) {
                    match ($request->estado_pago) {
                        'pendiente' => $inner->where('monto_pagado', 0),
                        'parcial'   => $inner->where('monto_pagado', '>', 0)->whereRaw('monto_pagado < monto_total'),
                        'pagado'    => $inner->whereRaw('monto_pagado >= monto_total'),
                        'con_saldo' => $inner->whereRaw('monto_pagado < monto_total'),
                        default     => null,
                    };
                });
            }

            if ($request->filled('buscar')) {
                $term = '%' . $request->buscar . '%';
                $q->whereHas('trabajo.cliente', fn($sub) => $sub
                    ->where('nombres_clientes',        'like', $term)
                    ->orWhere('apellidos_clientes',     'like', $term)
                    ->orWhere('razon_social',            'like', $term)
                    ->orWhere('identificacion_clientes', 'like', $term)
                );
            }

            if ($request->filled('vencimiento')) {
                match ($request->vencimiento) {
                    'vencidas'  => $q->whereNotNull('fecha_vencimiento')->where('fecha_vencimiento', '<', today()),
                    'proximas'  => $q->whereNotNull('fecha_vencimiento')
                                     ->whereBetween('fecha_vencimiento', [today(), today()->addDays(7)]),
                    'sin_fecha' => $q->whereNull('fecha_vencimiento'),
                    default     => null,
                };
            }
        };

        // Query principal con relaciones para la tabla
        $query = CuentaCobrar::with(['trabajo.cliente', 'trabajo.servicio', 'trabajo.departamento'])->latest();
        $applyFilters($query);

        // Queries limpios para KPIs — solo proyectos activos (excluye cancelados, solicitudes y rechazados)
        $kpiBase = CuentaCobrar::whereHas('trabajo', fn($q) => $q->whereNotIn('estado_trabajo', ['cancelado', 'solicitud', 'rechazado']));
        $applyFilters($kpiBase);

        // Alias "importe" en vez de "saldo" para evitar colisión con el accessor getSaldoAttribute()
        $kpiPendiente = (clone $kpiBase)
            ->toBase()
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(monto_total - monto_pagado), 0) as importe')
            ->whereRaw('monto_pagado < monto_total')
            ->first();

        $kpiVencidas = (clone $kpiBase)
            ->toBase()
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(monto_total - monto_pagado), 0) as importe')
            ->whereRaw('monto_pagado < monto_total')
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', today())
            ->first();

        $kpiPorVencer = (clone $kpiBase)
            ->toBase()
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(monto_total - monto_pagado), 0) as importe')
            ->whereRaw('monto_pagado < monto_total')
            ->whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [today(), today()->addDays(7)])
            ->first();

        $kpiTasa = (clone $kpiBase)
            ->toBase()
            ->selectRaw('COALESCE(SUM(monto_total), 0) as total, COALESCE(SUM(monto_pagado), 0) as pagado')
            ->first();
        $tasaPct = $kpiTasa->total > 0
            ? round($kpiTasa->pagado / $kpiTasa->total * 100, 1)
            : null;

        $cuentas = $query->paginate(15)->withQueryString();

        return view('cobros.index', compact(
            'cuentas',
            'kpiPendiente', 'kpiVencidas', 'kpiPorVencer', 'tasaPct'
        ));
    }

    public function show(CuentaCobrar $cobro)
    {
        abort_unless(auth()->user()->can('ver-cuenta-cobrar'), 403);

        $cobro->load([
            'trabajo.cliente',
            'trabajo.servicio',
            'trabajo.departamento',
            'trabajo.responsable',
            'trabajo.subtrabajos.servicio',
            'pagos.registrador',
        ]);

        $distribucion = $cobro->distribucion();
        $metodos      = \App\Models\Pago::metodosLabel();

        return view('cobros.show', compact('cobro', 'distribucion', 'metodos'));
    }

    public function update(Request $request, CuentaCobrar $cobro)
    {
        abort_unless(auth()->user()->can('editar-cuenta-cobrar'), 403);

        if ($cobro->trabajo->estado_trabajo === 'cancelado') {
            return back()->with('error', 'No se puede editar la cuenta de un proyecto cancelado.');
        }

        abort_if($cobro->saldo <= 0, 422);

        $request->validate([
            'fecha_vencimiento' => 'nullable|date',
            'notas'             => 'nullable|string|max:1000',
        ]);

        $cobro->update($request->only(['fecha_vencimiento', 'notas']));

        return back()->with('success', 'Cuenta actualizada correctamente.');
    }
}
