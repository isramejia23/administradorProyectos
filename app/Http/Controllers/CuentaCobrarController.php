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

        $query = CuentaCobrar::with(['trabajo.cliente', 'trabajo.servicio', 'trabajo.departamento'])
            ->latest();

        if ($request->filled('estado_pago')) {
            $query->where(function ($q) use ($request) {
                match ($request->estado_pago) {
                    'pendiente' => $q->where('monto_pagado', 0),
                    'parcial'   => $q->where('monto_pagado', '>', 0)->whereRaw('monto_pagado < monto_total'),
                    'pagado'    => $q->whereRaw('monto_pagado >= monto_total'),
                    default     => null,
                };
            });
        }

        if ($request->filled('buscar')) {
            $term = '%' . $request->buscar . '%';
            $query->whereHas('trabajo.cliente', fn($q) => $q
                ->where('nombres_clientes',         'like', $term)
                ->orWhere('apellidos_clientes',      'like', $term)
                ->orWhere('razon_social',             'like', $term)
                ->orWhere('identificacion_clientes',  'like', $term)
            );
        }

        if ($request->filled('vencimiento')) {
            match ($request->vencimiento) {
                'vencidas'  => $query->whereNotNull('fecha_vencimiento')->where('fecha_vencimiento', '<', today()),
                'proximas'  => $query->whereNotNull('fecha_vencimiento')
                                     ->whereBetween('fecha_vencimiento', [today(), today()->addDays(7)]),
                'sin_fecha' => $query->whereNull('fecha_vencimiento'),
                default     => null,
            };
        }

        $cuentas = $query->paginate(15)->withQueryString();

        return view('cobros.index', compact('cuentas'));
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
        abort_if($cobro->saldo <= 0, 422);

        $request->validate([
            'fecha_vencimiento' => 'nullable|date',
            'notas'             => 'nullable|string|max:1000',
        ]);

        $cobro->update($request->only(['fecha_vencimiento', 'notas']));

        return back()->with('success', 'Cuenta actualizada correctamente.');
    }
}
