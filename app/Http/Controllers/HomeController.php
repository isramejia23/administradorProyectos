<?php

namespace App\Http\Controllers;

use App\Models\Trabajo;
use App\Models\Departamento;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if (auth()->user()->can('ver-home-vendedor') && ! auth()->user()->can('ver-trabajo')) {
            return $this->homeVendedor();
        }

        $mes  = (int) $request->get('mes',  now()->month);
        $anio = (int) $request->get('anio', now()->year);

        $fecha  = Carbon::createFromDate($anio, $mes, 1);
        $inicio = $fecha->copy()->startOfMonth();
        $fin    = $fecha->copy()->endOfMonth();

        $user        = auth()->user();
        $esAdmin     = $user->can('ver-trabajo');
        $esJefe      = ! $esAdmin && $user->hasRole('Jefe de Departamento');
        $deptoId     = $user->departamento_id;
        $esMesActual = $fecha->isSameMonth(now());
        $hoy         = now()->startOfDay();

        // ── Scope base según rol ──────────────────────────────────────
        $base = Trabajo::query()
            ->when($esJefe,              fn($q) => $q->where('departamento_id', $deptoId))
            ->when(! $esAdmin && ! $esJefe, fn($q) => $q->where('responsable_id', $user->id));

        $pendientes  = (clone $base)->where('estado_trabajo', 'pendiente')
                            ->whereBetween('fecha_inicio', [$inicio, $fin])->count();
        $enProceso   = (clone $base)->where('estado_trabajo', 'proceso')
                            ->whereBetween('fecha_inicio', [$inicio, $fin])->count();
        $finalizados = (clone $base)->where('estado_trabajo', 'terminado')
                            ->whereBetween('fecha_fin', [$inicio, $fin])->count();
        $cancelados  = (clone $base)->where('estado_trabajo', 'cancelado')
                            ->whereBetween('fecha_inicio', [$inicio, $fin])->count();

        // ── Métricas adicionales (estado actual, sin filtro de mes) ───
        $baseActivos = Trabajo::whereIn('estado_trabajo', ['pendiente', 'proceso'])
                        ->when($esJefe,              fn($q) => $q->where('departamento_id', $deptoId))
                        ->when(! $esAdmin && ! $esJefe, fn($q) => $q->where('responsable_id', $user->id));

        $atrasadosCnt = (clone $baseActivos)
                        ->whereNotNull('fecha_estimada')
                        ->where('fecha_estimada', '<', $hoy)
                        ->count();

        $porVencer7Cnt = (clone $baseActivos)
                        ->whereNotNull('fecha_estimada')
                        ->whereBetween('fecha_estimada', [$hoy, $hoy->copy()->addDays(7)])
                        ->count();

        $sinResponsableCnt = ($esAdmin || $esJefe)
            ? Trabajo::whereIn('estado_trabajo', ['pendiente', 'proceso'])
                ->whereNull('responsable_id')
                ->when($esJefe, fn($q) => $q->where('departamento_id', $deptoId))
                ->count()
            : 0;

        $completadosATiempoCnt = (clone $base)
                        ->where('estado_trabajo', 'terminado')
                        ->whereBetween('fecha_fin', [$inicio, $fin])
                        ->whereNotNull('fecha_estimada')
                        ->whereColumn('fecha_fin', '<=', 'fecha_estimada')
                        ->count();

        $anterior  = $fecha->copy()->subMonth();
        $siguiente = $fecha->copy()->addMonth();

        // ── Por departamento ──────────────────────────────────────────
        $porDepartamento = ($esAdmin || $esJefe)
            ? Departamento::withCount([
                'trabajos as total_pendientes' => fn($q) => $q->where('estado_trabajo', 'pendiente'),
                'trabajos as total_proceso'    => fn($q) => $q->where('estado_trabajo', 'proceso'),
                'trabajos as total_terminados' => fn($q) => $q->where('estado_trabajo', 'terminado'),
                'trabajos as total_cancelados' => fn($q) => $q->where('estado_trabajo', 'cancelado'),
              ])
              ->when($esJefe, fn($q) => $q->where('id', $deptoId))
              ->orderBy('nombre_departamento')
              ->get()
            : collect();

        // ── Timeline: activos ordenados por riesgo ────────────────────
        $activos = Trabajo::with([
                        'cliente',
                        'servicio',
                        'subtrabajos' => fn($q) => $q->where('es_principal', false)
                                                      ->whereIn('estado', ['pendiente', 'proceso'])
                                                      ->with('servicio'),
                    ])
                    ->whereIn('estado_trabajo', ['pendiente', 'proceso'])
                    ->when($esJefe,              fn($q) => $q->where('departamento_id', $deptoId))
                    ->when(! $esAdmin && ! $esJefe, fn($q) => $q->where('responsable_id', $user->id))
                    ->orderBy('fecha_estimada')
                    ->limit(20)
                    ->get()
                    ->sortBy(function ($p) use ($hoy) {
                        $est = $p->fecha_estimada?->copy()->startOfDay();
                        if (! $est) return 3;
                        if ($hoy->gt($est)) return 0;
                        if ($hoy->diffInDays($est, false) <= 5) return 1;
                        return 2;
                    })
                    ->values();

        // ── Subtrabajos propios (profesionales) ───────────────────────
        $misSubtrabajos = (! $esAdmin && ! $esJefe)
            ? \App\Models\Subtrabajo::with(['trabajo.cliente', 'trabajo.servicio', 'servicio'])
                ->where('responsable_id', $user->id)
                ->where('es_principal', false)
                ->whereIn('estado', ['pendiente', 'proceso'])
                ->orderBy('fecha_estimada')
                ->limit(15)
                ->get()
            : collect();

        return view('home', compact(
            'pendientes', 'enProceso', 'finalizados', 'cancelados',
            'atrasadosCnt', 'porVencer7Cnt', 'sinResponsableCnt', 'completadosATiempoCnt',
            'fecha', 'anterior', 'siguiente', 'esMesActual',
            'activos', 'esAdmin', 'misSubtrabajos', 'porDepartamento'
        ));
    }

    private function homeVendedor()
    {
        $uid = auth()->id();
        $hoy = now()->startOfDay();

        $base = \App\Models\Trabajo::where('vendedor_id', $uid);

        $pendientes  = (clone $base)->where('estado_trabajo', 'pendiente')->count();
        $enProceso   = (clone $base)->where('estado_trabajo', 'proceso')->count();
        $terminados  = (clone $base)->where('estado_trabajo', 'terminado')->count();
        $cancelados  = (clone $base)->where('estado_trabajo', 'cancelado')->count();

        $comisionGanada   = (clone $base)->where('estado_trabajo', 'terminado')
                                ->whereNotNull('porcentaje_comision')
                                ->get()
                                ->sum(fn($t) => $t->monto_comision);

        $comisionPendiente = (clone $base)->whereIn('estado_trabajo', ['pendiente', 'proceso'])
                                ->whereNotNull('porcentaje_comision')
                                ->get()
                                ->sum(fn($t) => $t->monto_comision);

        $activos = (clone $base)->with(['cliente', 'servicio', 'subtrabajos'])
                        ->whereIn('estado_trabajo', ['pendiente', 'proceso'])
                        ->orderBy('fecha_estimada')
                        ->limit(20)
                        ->get();

        return view('home-vendedor', compact(
            'pendientes', 'enProceso', 'terminados', 'cancelados',
            'comisionGanada', 'comisionPendiente', 'activos', 'hoy'
        ));
    }
}
