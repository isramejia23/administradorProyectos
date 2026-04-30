<?php

namespace App\Http\Controllers;

use App\Models\Accion;
use App\Models\Subtrabajo;
use App\Models\Trabajo;
use Illuminate\Http\Request;

class AccionController extends Controller
{
    public function store(Request $request, Trabajo $proyecto, Subtrabajo $subtrabajo)
    {
        $this->authorize('create', [Accion::class, $subtrabajo]);

        $request->validate([
            'descripcion' => 'required|string',
            'fecha_fin'   => 'nullable|date',
            'estado'      => 'required|in:pendiente,proceso,terminado,cancelado',
            'observacion' => 'nullable|string|max:1000',
        ]);

        $subtrabajo->acciones()->create([
            'user_id'      => auth()->id(),
            'descripcion'  => $request->descripcion,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin'    => $request->fecha_fin,
            'estado'       => $request->estado,
            'observacion'  => $request->observacion,
        ]);

        $redirect = $proyecto->trabajo_unico
            ? redirect()->route('proyectos.show', $proyecto->id)
            : redirect()->route('subtrabajos.show', [$proyecto->id, $subtrabajo->id]);

        return $redirect->with('success', 'Acción registrada correctamente.');
    }

    public function update(Request $request, Trabajo $proyecto, Subtrabajo $subtrabajo, Accion $accion)
    {
        $this->authorize('update', $accion);

        $request->validate([
            'estado'      => 'required|in:pendiente,proceso,terminado,cancelado',
            'fecha_fin'   => 'nullable|date',
            'observacion' => 'nullable|string|max:1000',
        ]);

        $accion->update($request->only(['estado', 'fecha_fin', 'observacion']));

        $redirect = $proyecto->trabajo_unico
            ? redirect()->route('proyectos.show', $proyecto->id)
            : redirect()->route('subtrabajos.show', [$proyecto->id, $subtrabajo->id]);

        return $redirect->with('success', 'Acción actualizada.');
    }
}
