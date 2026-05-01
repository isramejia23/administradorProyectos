<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trabajo;
use App\Models\Subtrabajo;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Departamento;
use App\Models\User;
use App\Models\Historial;

class TrabajoController extends Controller
{
    private function formData(): array
    {
        return [
            'clientes'      => Cliente::where('estado', 'Activo')->orderBy('nombres_clientes')->get(),
            'servicios'     => Servicio::where('estatus', 'Activo')->orderBy('nombre_servicio')->get(),
            'departamentos' => Departamento::orderBy('nombre_departamento')->pluck('nombre_departamento', 'id'),
            'usuarios'      => User::where('estado', 'Activo')->orderBy('nombre')->get(),
            'estados'       => ['pendiente' => 'Pendiente', 'proceso' => 'En Proceso', 'terminado' => 'Terminado', 'cancelado' => 'Cancelado'],
            'urgencias'     => ['bajo' => 'Bajo', 'medio' => 'Medio', 'alto' => 'Alto'],
        ];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->can('ver-trabajo') || $user->can('ver-trabajo-departamento'), 403);

        $soloDepto = ! $user->can('ver-trabajo') && $user->can('ver-trabajo-departamento');

        $query = Trabajo::with(['cliente', 'servicio', 'departamento', 'vendedor', 'responsable'])
            ->whereNotIn('estado_trabajo', ['solicitud', 'rechazado']); // solicitudes van a su propia bandeja

        if ($soloDepto) {
            $query->where('departamento_id', $user->departamento_id);
        }

        if ($request->filled('buscar')) {
            $term = '%' . $request->buscar . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('cliente', fn($c) => $c->where('codigo_cliente', 'like', $term)
                    ->orWhere('nombres_clientes', 'like', $term)
                    ->orWhere('apellidos_clientes', 'like', $term)
                    ->orWhere('razon_social', 'like', $term)
                )
                ->orWhereHas('servicio', fn($s) => $s->where('nombre_servicio', 'like', $term));
            });
        }

        if ($request->filled('estado_trabajo')) {
            $query->where('estado_trabajo', $request->estado_trabajo);
        }

        if (! $soloDepto && $request->filled('departamento_id')) {
            $query->where('departamento_id', $request->departamento_id);
        }

        if ($request->filled('nivel_urgencia')) {
            $query->where('nivel_urgencia', $request->nivel_urgencia);
        }

        if ($request->filled('responsable_id')) {
            if ($request->responsable_id === 'null') {
                $query->whereNull('responsable_id');
            } else {
                $query->where('responsable_id', $request->responsable_id);
            }
        }

        if ($request->filled('trabajo_unico')) {
            $query->where('trabajo_unico', $request->trabajo_unico);
        }

        $proyectos     = $query->latest()->paginate(15)->withQueryString();
        $departamentos = $soloDepto
            ? Departamento::where('id', $user->departamento_id)->pluck('nombre_departamento', 'id')
            : Departamento::orderBy('nombre_departamento')->pluck('nombre_departamento', 'id');
        $usuarios      = $soloDepto
            ? User::where('estado', 'Activo')->where('departamento_id', $user->departamento_id)->orderBy('nombre')->get()
            : User::where('estado', 'Activo')->orderBy('nombre')->get();

        return view('proyectos.index', compact('proyectos', 'departamentos', 'usuarios', 'soloDepto'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Trabajo::class);

        $user        = auth()->user();
        $esVendedor  = ! $user->hasRole(['Super Admin', 'Administrador']);
        $clienteId   = $request->query('cliente_id');

        return view('proyectos.crear', array_merge(
            $this->formData(),
            ['clienteIdPreseleccionado' => $clienteId, 'esVendedor' => $esVendedor]
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Trabajo::class);

        $user       = auth()->user();
        $esVendedor = ! $user->hasRole(['Super Admin', 'Administrador']);

        if ($esVendedor) {
            $request->validate([
                'cliente_id'         => 'required|exists:clientes,id',
                'servicio_id'        => 'required|exists:servicios,id',
                'departamento_id'    => 'required|exists:departamentos,id',
                'monto_total'        => 'required|numeric|min:0',
                'nivel_urgencia'     => 'required|in:bajo,medio,alto',
                'razon'              => 'required|string',
                'resultado_esperado' => 'required|string|max:1000',
                'acuerdo_pagos'      => 'nullable|string|max:500',
                'fecha_estimada'     => 'nullable|date',
            ]);

            $proyecto = Trabajo::create([
                'cliente_id'         => $request->cliente_id,
                'servicio_id'        => $request->servicio_id,
                'departamento_id'    => $request->departamento_id,
                'vendedor_id'        => $user->id,
                'responsable_id'     => $user->can('gestionar-trabajo') ? $user->id : null,
                'monto_total'        => $request->monto_total,
                'nivel_urgencia'     => $request->nivel_urgencia,
                'razon'              => $request->razon,
                'resultado_esperado' => $request->resultado_esperado,
                'acuerdo_pagos'      => $request->acuerdo_pagos,
                'fecha_estimada'     => $request->fecha_estimada,
                'estado_trabajo'     => 'solicitud',
                'trabajo_unico'      => $request->boolean('trabajo_unico'),
            ]);

            return redirect()->route('proyectos.show', $proyecto->id)
                ->with('success', 'Solicitud enviada correctamente. Queda pendiente de aprobación.');
        }

        // Flujo normal para roles con crear-trabajo
        $request->validate([
            'cliente_id'          => 'required|exists:clientes,id',
            'servicio_id'         => 'required|exists:servicios,id',
            'vendedor_id'         => 'nullable|exists:users,id',
            'porcentaje_comision' => 'nullable|numeric|min:0|max:100',
            'responsable_id'      => 'nullable|exists:users,id',
            'departamento_id'     => 'required|exists:departamentos,id',
            'numero_tramite'      => 'nullable|string|max:20',
            'monto_total'         => 'required|numeric|min:0',
            'estado_trabajo'      => 'required|in:pendiente,proceso,terminado,cancelado',
            'nivel_urgencia'      => 'required|in:bajo,medio,alto',
            'fecha_inicio'        => 'required|date',
            'fecha_estimada'      => 'required|date',
            'fecha_fin'           => 'nullable|date',
            'razon'               => 'required|string',
            'resultado_esperado'  => 'required|string|max:1000',
            'acuerdo_pagos'       => 'required|string|max:500',
        ]);

        $data = $request->except('trabajo_unico');
        $data['trabajo_unico'] = $request->boolean('trabajo_unico');

        $proyecto = Trabajo::create($data);

        if ($proyecto->trabajo_unico) {
            $proyecto->crearSubtrabajoPrincipal();
        }

        return redirect()->route('proyectos.show', $proyecto->id)
            ->with('success', 'Proyecto creado exitosamente.');
    }

    public function show(Trabajo $proyecto)
    {
        // Solicitudes tienen su propia vista
        if ($proyecto->esSolicitud()) {
            $this->authorize('view', $proyecto);
            $proyecto->load(['cliente', 'servicio', 'departamento', 'vendedor']);
            $historialSolicitud = $proyecto->historial()->with('usuario')->get();
            return view('proyectos.show-solicitud', compact('proyecto', 'historialSolicitud'));
        }

        $usuarios             = User::where('estado', 'Activo')->orderBy('nombre')->get();
        $usuariosDepartamento = User::where('estado', 'Activo')
                                    ->where('departamento_id', $proyecto->departamento_id)
                                    ->orderBy('nombre')
                                    ->get();

        if ($proyecto->trabajo_unico) {
            $proyecto->load(['cliente', 'servicio', 'departamento', 'vendedor', 'responsable',
                             'subtrabajoPrincipal.acciones.usuario']);
            $subtrabajoPrincipal = $proyecto->subtrabajoPrincipal;

            return view('proyectos.show-unico', compact('proyecto', 'subtrabajoPrincipal', 'usuarios', 'usuariosDepartamento'));
        }

        $proyecto->load(['cliente', 'servicio', 'departamento', 'vendedor', 'responsable',
                         'subtrabajos.responsable', 'subtrabajos.departamento', 'subtrabajos.servicio',
                         'subtrabajos.historial.usuario', 'cuentaCobrar']);
        $departamentos = Departamento::orderBy('nombre_departamento')->get();
        $servicios     = Servicio::where('estatus', 'Activo')->orderBy('nombre_servicio')->get();
        $historial     = $proyecto->historial()->with('usuario')->get();

        return view('proyectos.show', compact('proyecto', 'departamentos', 'usuarios', 'servicios', 'usuariosDepartamento', 'historial'));
    }

    public function edit(Trabajo $proyecto)
    {
        $this->authorize('update', $proyecto);
        return view('proyectos.editar', array_merge($this->formData(), compact('proyecto')));
    }

    public function update(Request $request, Trabajo $proyecto)
    {
        $this->authorize('update', $proyecto);
        $request->validate([
            'cliente_id'          => 'required|exists:clientes,id',
            'servicio_id'         => 'required|exists:servicios,id',
            'vendedor_id'         => 'nullable|exists:users,id',
            'porcentaje_comision' => 'nullable|numeric|min:0|max:100',
            'responsable_id'      => 'nullable|exists:users,id',
            'departamento_id'     => 'required|exists:departamentos,id',
            'numero_tramite'      => 'nullable|string|max:20',
            'monto_total'         => 'required|numeric|min:0',
            'estado_trabajo'      => 'required|in:pendiente,proceso,terminado,cancelado',
            'nivel_urgencia'      => 'required|in:bajo,medio,alto',
            'fecha_inicio'        => 'required|date',
            'fecha_estimada'      => 'required|date',
            'fecha_fin'           => 'nullable|date',
            'razon'               => 'required|string',
            'resultado_esperado'  => 'required|string|max:1000',
            'acuerdo_pagos'       => 'required|string|max:500',
        ]);

        $data = $request->except('trabajo_unico');
        $data['trabajo_unico'] = $request->boolean('trabajo_unico');

        $proyecto->update($data);

        return redirect()->route('proyectos.index')->with('success', 'Proyecto actualizado correctamente.');
    }

    public function destroy(Trabajo $proyecto)
    {
        if ($proyecto->cuentaCobrar()->exists()) {
            return redirect()->route('proyectos.show', $proyecto->id)
                ->with('error', 'No se puede eliminar un proyecto que tiene una cuenta por cobrar registrada.');
        }

        $proyecto->delete();
        return redirect()->route('proyectos.index')->with('success', 'Proyecto eliminado exitosamente.');
    }

    public function finalizar(Trabajo $proyecto)
    {
        $this->authorize('gestionar', $proyecto);

        $proyecto->update([
            'estado_trabajo' => 'terminado',
            'fecha_fin'      => now()->toDateString(),
        ]);

        return redirect()->route('proyectos.show', $proyecto->id)
            ->with('success', 'Proyecto marcado como finalizado.');
    }

    public function gestionar(Request $request, Trabajo $proyecto)
    {
        $this->authorize('gestionar', $proyecto);

        $request->validate([
            'estado_trabajo'     => 'required|in:pendiente,proceso,terminado,cancelado',
            'fecha_fin'          => 'nullable|date',
            'razon'              => 'nullable|string',
            'resultado_esperado' => 'nullable|string|max:1000',
        ]);

        $proyecto->update($request->only(['estado_trabajo', 'fecha_fin', 'razon', 'resultado_esperado']));

        return redirect()->route('proyectos.show', $proyecto->id)
            ->with('success', 'Proyecto actualizado correctamente.');
    }

    public function asignarVendedor(Request $request, Trabajo $proyecto)
    {
        abort_unless(auth()->user()->can('asignar-vendedor'), 403);

        $request->validate([
            'vendedor_id' => 'nullable|exists:users,id',
        ]);

        $proyecto->update(['vendedor_id' => $request->vendedor_id ?: null]);

        return back()->with('success', 'Vendedor asignado correctamente.');
    }

    public function actualizarComision(Request $request, Trabajo $proyecto)
    {
        abort_unless(auth()->user()->can('editar-comision'), 403);

        $request->validate([
            'porcentaje_comision' => 'nullable|numeric|min:0|max:100',
        ]);

        $proyecto->update(['porcentaje_comision' => $request->porcentaje_comision]);

        return back()->with('success', 'Comisión actualizada correctamente.');
    }

    public function actualizarTramite(Request $request, Trabajo $proyecto)
    {
        $this->authorize('gestionar', $proyecto);

        $request->validate(['numero_tramite' => 'nullable|string|max:20']);

        $proyecto->update(['numero_tramite' => $request->numero_tramite]);

        return redirect()->route('proyectos.show', $proyecto->id)
            ->with('success', 'Número de trámite actualizado.');
    }

    public function aceptar(Trabajo $proyecto)
    {
        $this->authorize('aceptar', $proyecto);

        $proyecto->update(['responsable_id' => auth()->id()]);

        if ($proyecto->trabajo_unico) {
            $proyecto->subtrabajoPrincipal()->update(['responsable_id' => auth()->id()]);
        }

        return redirect()->route('proyectos.mis')
            ->with('success', 'Has aceptado el proyecto #' . $proyecto->id . '. Ahora aparece en Mis Proyectos.');
    }

    public function asignarResponsable(Request $request, Trabajo $proyecto)
    {
        $this->authorize('asignarResponsable', $proyecto);

        $request->validate([
            'responsable_id' => 'required|exists:users,id',
        ]);

        $proyecto->update(['responsable_id' => $request->responsable_id]);

        return redirect()->route('proyectos.show', $proyecto->id)
            ->with('success', 'Responsable asignado correctamente.');
    }

    public function bandeja()
    {
        abort_unless(auth()->user()->can('ver-bandeja-trabajo'), 403);

        $user    = auth()->user();
        $deptoId = $user->departamento_id;

        $proyectos = Trabajo::with(['cliente', 'servicio', 'departamento'])
            ->whereNull('responsable_id')
            ->whereNotIn('estado_trabajo', ['solicitud', 'rechazado'])
            ->when(! $user->veeTodosLosDepartamentos(), fn($q) => $q->where('departamento_id', $deptoId))
            ->latest()
            ->paginate(15, ['*'], 'proy');

        $subtrabajos = Subtrabajo::with(['trabajo.cliente', 'servicio', 'departamento'])
            ->whereNull('responsable_id')
            ->where('es_principal', false)
            ->when(! $user->veeTodosLosDepartamentos(), fn($q) => $q->where('departamento_id', $deptoId))
            ->latest()
            ->paginate(15, ['*'], 'sub');

        $solicitudes = null;
        if ($user->can('ver-solicitudes-trabajo')) {
            $solicitudes = Trabajo::with(['cliente', 'servicio', 'departamento', 'vendedor'])
                ->where('estado_trabajo', 'solicitud')
                ->when(! $user->veeTodosLosDepartamentos(), fn($q) => $q->where('departamento_id', $deptoId))
                ->latest()
                ->paginate(15, ['*'], 'sol');
        }

        return view('proyectos.bandeja', compact('proyectos', 'subtrabajos', 'solicitudes'));
    }

    public function misVentas(Request $request)
    {
        abort_unless(auth()->user()->can('ver-mis-ventas'), 403);

        $query = Trabajo::with(['cliente', 'servicio', 'subtrabajos'])
            ->where('vendedor_id', auth()->id());

        if ($request->filled('buscar')) {
            $term = '%' . $request->buscar . '%';
            $query->whereHas('cliente', fn($q) => $q
                ->where('nombres_clientes',          'like', $term)
                ->orWhere('apellidos_clientes',       'like', $term)
                ->orWhere('razon_social',             'like', $term)
                ->orWhere('identificacion_clientes',  'like', $term)
            );
        }

        if ($request->filled('estado_trabajo')) {
            $query->where('estado_trabajo', $request->estado_trabajo);
        }

        $proyectos = $query->latest()->paginate(15)->withQueryString();

        return view('proyectos.mis-ventas', compact('proyectos'));
    }

    public function vendedores(Request $request)
    {
        abort_unless(auth()->user()->can('ver-ventas-admin'), 403);

        $query = Trabajo::with(['cliente', 'servicio', 'vendedor', 'subtrabajos'])
            ->withCount('subtrabajos')
            ->whereNotIn('estado_trabajo', ['solicitud', 'rechazado']);

        if ($request->filled('vendedor_id')) {
            if ($request->vendedor_id === 'null') {
                $query->whereNull('vendedor_id');
            } else {
                $query->where('vendedor_id', $request->vendedor_id);
            }
        }

        if ($request->filled('estado_trabajo')) {
            $query->where('estado_trabajo', $request->estado_trabajo);
        }

        if ($request->filled('buscar')) {
            $term = '%' . $request->buscar . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('cliente', fn($c) => $c
                    ->where('nombres_clientes', 'like', $term)
                    ->orWhere('apellidos_clientes', 'like', $term)
                    ->orWhere('razon_social', 'like', $term)
                )
                ->orWhereHas('servicio', fn($s) => $s->where('nombre_servicio', 'like', $term));
            });
        }

        $proyectos = $query->latest()->paginate(15)->withQueryString();
        $usuarios  = User::where('estado', 'Activo')->orderBy('nombre')->get();

        return view('proyectos.vendedores', compact('proyectos', 'usuarios'));
    }

    public function misProyectos(Request $request)
    {
        abort_unless(auth()->user()->can('ver-mis-trabajos'), 403);

        $query = Trabajo::with(['cliente', 'servicio'])
            ->where('responsable_id', auth()->id());

        if ($request->filled('buscar')) {
            $term = '%' . $request->buscar . '%';
            $query->whereHas('cliente', fn($q) => $q
                ->where('nombres_clientes',   'like', $term)
                ->orWhere('apellidos_clientes','like', $term)
                ->orWhere('razon_social',      'like', $term)
                ->orWhere('identificacion_clientes', 'like', $term)
            );
        }

        if ($request->filled('servicio')) {
            $query->whereHas('servicio', fn($q) =>
                $q->where('nombre_servicio', 'like', '%' . $request->servicio . '%')
            );
        }

        if ($request->filled('estado_trabajo')) {
            $query->where('estado_trabajo', $request->estado_trabajo);
        }

        if ($request->filled('nivel_urgencia')) {
            $query->where('nivel_urgencia', $request->nivel_urgencia);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_inicio', $request->fecha_inicio);
        }

        if ($request->filled('trabajo_unico')) {
            $query->where('trabajo_unico', $request->trabajo_unico);
        }

        $proyectos = $query->latest()->paginate(15)->withQueryString();

        return view('proyectos.mis-proyectos', compact('proyectos'));
    }

    // ── Flujo de solicitudes de vendedores ────────────────────────────────────

    /** Bandeja de solicitudes pendientes de aprobación */
    public function solicitudes(Request $request)
    {
        abort_unless(auth()->user()->can('ver-solicitudes-trabajo'), 403);

        $user      = auth()->user();
        $soloDepto = ! $user->hasRole(['Super Admin', 'Administrador']) && $user->hasRole('Jefe de Departamento');

        $query = Trabajo::with(['cliente', 'servicio', 'departamento', 'vendedor'])
            ->whereIn('estado_trabajo', ['solicitud', 'rechazado']);

        if ($soloDepto) {
            $query->where('departamento_id', $user->departamento_id);
        }

        if ($request->filled('estado_trabajo')) {
            $query->where('estado_trabajo', $request->estado_trabajo);
        }

        if ($request->filled('buscar')) {
            $term = '%' . $request->buscar . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('cliente', fn($c) => $c
                    ->where('nombres_clientes', 'like', $term)
                    ->orWhere('apellidos_clientes', 'like', $term)
                    ->orWhere('razon_social', 'like', $term)
                )
                ->orWhereHas('vendedor', fn($v) => $v
                    ->where('nombre', 'like', $term)
                    ->orWhere('apellido', 'like', $term)
                );
            });
        }

        $proyectos = $query->latest()->paginate(15)->withQueryString();

        return view('proyectos.solicitudes', compact('proyectos', 'soloDepto'));
    }

    /** Formulario de aprobación: el aprobador completa los campos que el vendedor no llenó */
    public function mostrarAprobar(Trabajo $proyecto)
    {
        $this->authorize('aprobar', $proyecto);

        $proyecto->load(['cliente', 'servicio', 'departamento', 'vendedor']);

        return view('proyectos.aprobar', array_merge($this->formData(), compact('proyecto')));
    }

    /** Confirmar aprobación: completa el proyecto y lo pasa a estado pendiente */
    public function confirmarAprobacion(Request $request, Trabajo $proyecto)
    {
        $this->authorize('aprobar', $proyecto);

        $request->validate([
            'departamento_id'     => 'required|exists:departamentos,id',
            'vendedor_id'         => 'nullable|exists:users,id',
            'porcentaje_comision' => 'nullable|numeric|min:0|max:100',
            'responsable_id'      => 'nullable|exists:users,id',
            'numero_tramite'      => 'nullable|string|max:20',
            'monto_total'         => 'required|numeric|min:0',
            'nivel_urgencia'      => 'required|in:bajo,medio,alto',
            'fecha_inicio'        => 'required|date',
            'fecha_estimada'      => 'required|date',
            'fecha_fin'           => 'nullable|date',
            'razon'               => 'required|string',
            'resultado_esperado'  => 'required|string|max:1000',
            'acuerdo_pagos'       => 'required|string|max:500',
        ]);

        $data                  = $request->except('trabajo_unico');
        $data['trabajo_unico'] = $request->boolean('trabajo_unico');
        $data['estado_trabajo'] = 'pendiente';
        $data['motivo_rechazo'] = null;

        $proyecto->update($data);

        $proyecto->historial()->create([
            'user_id'        => auth()->id(),
            'campo'          => 'estado_trabajo',
            'valor_anterior' => 'solicitud',
            'valor_nuevo'    => 'pendiente',
            'created_at'     => now(),
        ]);

        if (! $proyecto->cuentaCobrar()->exists()) {
            \App\Models\CuentaCobrar::create([
                'trabajo_id'   => $proyecto->id,
                'monto_base'   => $proyecto->monto_total,
                'monto_extras' => 0,
                'monto_total'  => $proyecto->monto_total,
                'monto_pagado' => 0,
            ]);
        }

        if ($proyecto->trabajo_unico) {
            $proyecto->crearSubtrabajoPrincipal();
        }

        return redirect()->route('proyectos.show', $proyecto->id)
            ->with('success', 'Solicitud aprobada. El proyecto está ahora en estado Pendiente.');
    }

    /** Rechazar una solicitud de vendedor */
    public function rechazar(Request $request, Trabajo $proyecto)
    {
        $this->authorize('aprobar', $proyecto);

        $request->validate([
            'motivo_rechazo' => 'required|string|max:1000',
        ]);

        $proyecto->update([
            'estado_trabajo'  => 'rechazado',
            'motivo_rechazo'  => $request->motivo_rechazo,
        ]);

        $proyecto->historial()->create([
            'user_id'        => auth()->id(),
            'campo'          => 'estado_trabajo',
            'valor_anterior' => 'solicitud',
            'valor_nuevo'    => 'rechazado',
            'created_at'     => now(),
        ]);

        return redirect()->route('proyectos.solicitudes')
            ->with('success', 'Solicitud rechazada. Se notificó el motivo al vendedor.');
    }
}
