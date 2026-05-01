<?php

namespace App\Http\Controllers;

use App\Models\CuentaCobrar;
use App\Models\Pago;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function store(Request $request, CuentaCobrar $cobro)
    {
        abort_unless(auth()->user()->can('crear-pago'), 403);

        if ($cobro->trabajo->estado_trabajo === 'cancelado') {
            return back()->with('error', 'No se pueden registrar pagos en un proyecto cancelado.');
        }

        $request->validate([
            'monto'       => ['required', 'numeric', 'min:0.01', 'max:' . $cobro->saldo],
            'fecha_pago'  => 'required|date',
            'metodo_pago' => 'required|in:efectivo,transferencia,cheque,tarjeta',
            'referencia'  => 'nullable|string|max:100',
            'notas'       => 'nullable|string|max:500',
        ], [
            'monto.max' => 'El monto no puede superar el saldo pendiente ($' . number_format($cobro->saldo, 2) . ').',
        ]);

        $cobro->pagos()->create([
            'monto'          => $request->monto,
            'fecha_pago'     => $request->fecha_pago,
            'metodo_pago'    => $request->metodo_pago,
            'referencia'     => $request->referencia,
            'notas'          => $request->notas,
            'registrado_por' => auth()->id(),
        ]);

        return back()->with('success', 'Pago registrado correctamente.');
    }

    public function update(Request $request, CuentaCobrar $cobro, Pago $pago)
    {
        abort_unless(auth()->user()->can('editar-pago'), 403);
        abort_unless($pago->cuenta_cobrar_id === $cobro->id, 404);

        if ($cobro->trabajo->estado_trabajo === 'cancelado') {
            return back()->with('error', 'No se pueden editar pagos en un proyecto cancelado.');
        }

        if ($pago->is_anulado) {
            return back()->with('error', 'No se puede editar un pago que ya fue anulado.');
        }

        $maxMonto = $cobro->saldo + (float) $pago->monto;

        $request->validate([
            'monto'       => ['required', 'numeric', 'min:0.01', 'max:' . $maxMonto],
            'fecha_pago'  => 'required|date',
            'metodo_pago' => 'required|in:efectivo,transferencia,cheque,tarjeta',
            'referencia'  => 'nullable|string|max:100',
            'notas'       => 'nullable|string|max:500',
        ], [
            'monto.max' => 'El monto no puede superar el total disponible ($' . number_format($maxMonto, 2) . ').',
        ]);

        $pago->update($request->only(['monto', 'fecha_pago', 'metodo_pago', 'referencia', 'notas']));

        return back()->with('success', 'Pago actualizado correctamente.');
    }

    public function anular(Request $request, CuentaCobrar $cobro, Pago $pago)
    {
        abort_unless(auth()->user()->can('borrar-pago'), 403);
        abort_unless($pago->cuenta_cobrar_id === $cobro->id, 404);

        if ($pago->is_anulado) {
            return back()->with('error', 'Este pago ya fue anulado anteriormente.');
        }

        $request->validate([
            'motivo_anulacion' => 'required|string|max:500',
        ], [
            'motivo_anulacion.required' => 'Debe indicar el motivo de anulación.',
        ]);

        $pago->update([
            'anulado_at'       => now(),
            'motivo_anulacion' => $request->motivo_anulacion,
        ]);

        return back()->with('success', 'Pago anulado correctamente.');
    }
}
