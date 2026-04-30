<?php

namespace App\Http\Controllers;

use App\Models\Subtrabajo;
use App\Models\Trabajo;
use Illuminate\Http\Request;
use App\Models\User;

class SubtrabajoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->can('ver-subtrabajo'), 403);

        $soloDepto = $user->hasRole('Jefe de Departamento');

        $query = Subtrabajo::with(['trabajo.cliente', 'departamento', 'servicio', 'responsable'])
            ->where('es_principal', false);

        if ($soloDepto) {
            $query->where('departamento_id', $user->departamento_id);
        } elseif ($request->filled('departamento_id')) {
            $query->where('departamento_id', $request->departamento_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('responsable_id')) {
            if ($request->responsable_id === 'null') {
                $query->whereNull('responsable_id');
            } else {
                $query->where('responsable_id', $request->responsable_id);
            }
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_inicio', $request->fecha_inicio);
        }

        $subtrabajos   = $query->latest()->paginate(20)->withQueryString();
        $departamentos = $soloDepto
            ? \App\Models\Departamento::where('id', $user->departamento_id)->get()
            : \App\Models\Departamento::orderBy('nombre_departamento')->get();
        $usuarios      = $soloDepto
            ? \App\Models\User::where('estado', 'Activo')->where('departamento_id', $user->departamento_id)->orderBy('nombre')->get()
            : \App\Models\User::where('estado', 'Activo')->orderBy('nombre')->get();

        return view('subtrabajos.index', compact('subtrabajos', 'departamentos', 'usuarios', 'soloDepto'));
    }

    public function misSubtrabajos(Request $request)
    {
        abort_unless(auth()->user()->can('ver-mis-trabajos'), 403);

        $query = Subtrabajo::with(['trabajo.cliente', 'trabajo.servicio', 'departamento', 'servicio'])
            ->where('responsable_id', auth()->id())
            ->where('es_principal', false);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('departamento_id')) {
            $query->where('departamento_id', $request->departamento_id);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_inicio', $request->fecha_inicio);
        }

        $subtrabajos   = $query->latest()->paginate(15)->withQueryString();
        $departamentos = \App\Models\Departamento::orderBy('nombre_departamento')->get();

        return view('subtrabajos.mis-subtrabajos', compact('subtrabajos', 'departamentos'));
    }

    public function show(Trabajo $proyecto, Subtrabajo $subtrabajo)
    {
        $subtrabajo->load(['departamento', 'servicio', 'responsable', 'acciones.usuario', 'acciones.subtrabajo', 'trabajo', 'historial.usuario']);
        $proyecto->load(['cliente', 'servicio', 'departamento', 'responsable']);

        $usuariosDepartamento = \App\Models\User::where('estado', 'Activo')
                                    ->where('departamento_id', $subtrabajo->departamento_id)
                                    ->orderBy('nombre')
                                    ->get();

        $departamentos = \App\Models\Departamento::orderBy('nombre_departamento')->get();
        $servicios = \App\Models\Servicio::where('estatus', 'Activo')->orderBy('nombre_servicio')->get();
        $usuarios = \App\Models\User::where('estado', 'Activo')->orderBy('nombre')->get();

        $historial = $subtrabajo->historial;

        return view('subtrabajos.show', compact('proyecto', 'subtrabajo', 'usuariosDepartamento', 'departamentos', 'servicios', 'usuarios', 'historial'));
    }

    public function store(Request $request, Trabajo $proyecto)
    {
        $this->authorize('create', [Subtrabajo::class, $proyecto]);

        $data = $request->validate([
            'departamento_id'    => 'required|exists:departamentos,id',
            'servicio_id'        => 'nullable|exists:servicios,id',
            'responsable_id'     => 'nullable|exists:users,id',
            'numero_tramite'     => 'nullable|string|max:20',
            'costo_especialista' => 'nullable|numeric|min:0',
            'precio_incluido'    => 'nullable|boolean',
            'fecha_inicio'       => 'nullable|date',
            'fecha_estimada'     => 'nullable|date',
            'estado'             => 'required|in:pendiente,proceso,terminado,cancelado',
            'observaciones'      => 'nullable|string|max:1000',
        ]);

        $data['trabajo_id']          = $proyecto->id;
        $data['costo_especialista'] ??= 0;
        $data['precio_incluido']     = $request->boolean('precio_incluido');

        $proyecto->subtrabajos()->create($data);

        return redirect()->route('proyectos.show', $proyecto->id)
            ->with('success', 'Subtrabajo agregado correctamente.');
    }

    public function aceptar(Trabajo $proyecto, Subtrabajo $subtrabajo)
    {
        $this->authorize('aceptar', $subtrabajo);

        $subtrabajo->update([
            'responsable_id' => auth()->id(),
            'estado'         => 'proceso',
            'fecha_inicio'   => $subtrabajo->fecha_inicio ?? now(),
        ]);

        return redirect()->route('proyectos.show', $proyecto->id)
            ->with('success', 'Has aceptado el subtrabajo. Ahora aparece en el proyecto.');
    }

    public function asignarResponsable(Request $request, Trabajo $proyecto, Subtrabajo $subtrabajo)
    {
        $this->authorize('asignarResponsable', $subtrabajo);

        $request->validate([
            'responsable_id' => 'required|exists:users,id',
        ]);

        $subtrabajo->update([
            'responsable_id' => $request->responsable_id,
            'estado'         => 'proceso',
            'fecha_inicio'   => $subtrabajo->fecha_inicio ?? now(),
        ]);

        return redirect()->route('subtrabajos.show', [$proyecto->id, $subtrabajo->id])
            ->with('success', 'Responsable asignado correctamente.');
    }

    public function actualizarTramite(Request $request, Trabajo $proyecto, Subtrabajo $subtrabajo)
    {
        $this->authorize('update', $subtrabajo);

        $request->validate(['numero_tramite' => 'nullable|string|max:20']);

        $subtrabajo->update(['numero_tramite' => $request->numero_tramite]);

        return redirect()->route('subtrabajos.show', [$proyecto->id, $subtrabajo->id])
            ->with('success', 'Número de trámite actualizado.');
    }

    public function finalizar(Trabajo $proyecto, Subtrabajo $subtrabajo)
    {
        $this->authorize('finalizar', $subtrabajo);

        $pendientes = $subtrabajo->acciones()
            ->whereIn('estado', ['pendiente', 'proceso'])
            ->count();

        if ($pendientes > 0) {
            $route = $proyecto->trabajo_unico
                ? redirect()->route('proyectos.show', $proyecto->id)
                : redirect()->route('subtrabajos.show', [$proyecto->id, $subtrabajo->id]);

            return $route->with('warning',
                "Hay {$pendientes} " . ($pendientes === 1 ? 'acción' : 'acciones') .
                " en pendiente o en proceso. Resuélvelas antes de finalizar."
            );
        }

        $fechaFin = now()->toDateString();

        $subtrabajo->update([
            'estado'    => 'terminado',
            'fecha_fin' => $fechaFin,
        ]);

        if ($proyecto->trabajo_unico) {
            $proyecto->update([
                'estado_trabajo' => 'terminado',
                'fecha_fin'      => $fechaFin,
            ]);

            return redirect()->route('proyectos.show', $proyecto->id)
                ->with('success', 'Proyecto finalizado correctamente.');
        }

        return redirect()->route('subtrabajos.show', [$proyecto->id, $subtrabajo->id])
            ->with('success', 'Subtrabajo marcado como finalizado.');
    }

    public function update(Request $request, Trabajo $proyecto, Subtrabajo $subtrabajo)
    {
        abort_unless(auth()->user()->can('editar-subtrabajo'), 403);

        $data = $request->validate([
            'departamento_id'    => 'required|exists:departamentos,id',
            'servicio_id'        => 'nullable|exists:servicios,id',
            'responsable_id'     => 'nullable|exists:users,id',
            'numero_tramite'     => 'nullable|string|max:20',
            'costo_especialista' => 'nullable|numeric|min:0',
            'precio_incluido'    => 'nullable|boolean',
            'fecha_inicio'       => 'nullable|date',
            'fecha_estimada'     => 'nullable|date',
            'estado'             => 'required|in:pendiente,proceso,terminado,cancelado',
            'observaciones'      => 'nullable|string|max:1000',
        ]);

        $data['costo_especialista'] ??= 0;
        $data['precio_incluido'] = $request->boolean('precio_incluido');

        $subtrabajo->update($data);

        return redirect()->route('subtrabajos.show', [$proyecto->id, $subtrabajo->id])
            ->with('success', 'Subtrabajo editado correctamente.');
    }
}