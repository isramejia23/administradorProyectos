@extends('layouts.app')

@push('styles')
<style>
    /* Fondo dashboard */
    #mainContent { background: #f0f2f5; }

    /* Cards base */
    .dash-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
    }

    /* KPI cards */
    .kpi-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
        padding: 14px 16px;
        border-top: 4px solid var(--kpi-color);
        transition: transform .15s ease, box-shadow .15s ease;
        text-decoration: none;
        display: block;
        color: inherit;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,.11);
        color: inherit;
    }
    .kpi-icon {
        width: 38px; height: 38px; border-radius: 50%;
        background: color-mix(in srgb, var(--kpi-color) 12%, transparent);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .kpi-icon i { font-size: 1rem; color: var(--kpi-color); }
    .kpi-label { font-size: .65rem; color: #999; margin-bottom: 1px; }
    .kpi-value { font-size: 1.55rem; font-weight: 700; line-height: 1; color: #1a1a1a; }
    .kpi-value.alert-value { color: var(--kpi-color); }

    /* Depto cards */
    .depto-card {
        background: #fff; border-radius: 10px;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
        border-top: 4px solid var(--d-color);
        transition: transform .15s ease, box-shadow .15s ease;
        cursor: pointer;
    }
    .depto-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.11); }

    /* Metric bar */
    .metric-track { background:#e9ecef; border-radius:4px; height:7px; overflow:hidden; }
    .metric-fill  { height:100%; border-radius:4px; transition: width .7s ease; }

    /* Timeline rows */
    .tl-row { padding: 9px 12px; border-radius: 8px; margin-bottom: 8px; border-left: 3px solid transparent; }
    .tl-atrasado  { background:#fff5f5; border-left-color:#dc3545; }
    .tl-porvencer { background:#fffbf3; border-left-color:#fd7e14; }
    .tl-proceso   { background:#f7fbff; border-left-color:#0d6efd; }
    .tl-pendiente { background:#fafafa; border-left-color:#dee2e6; }

    .grupo-sep {
        display:flex; align-items:center; gap:8px;
        font-size:.6rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em;
        margin: 14px 0 6px; color: var(--g-color);
    }
    .grupo-sep::after { content:''; flex:1; height:1px; background:currentColor; opacity:.2; }

    /* Modal tabla override */
    .modal .table thead th {
        background-color: #f8f9fa !important; color: #555 !important;
        text-transform: none !important; font-size: .78rem !important;
        font-weight: 600 !important; letter-spacing: 0 !important;
        padding: .5rem .75rem !important; border-bottom: 2px solid #dee2e6 !important;
    }
    .modal .table tbody td { padding: .55rem .75rem !important; font-size: .88rem !important; }

    /* Section label */
    .section-label {
        font-size: .68rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .08em; color: #888; margin-bottom: 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

{{-- ══════════════════════════════════════════
     HEADER DASHBOARD
═══════════════════════════════════════════ --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="font-size:1.25rem;">Dashboard</h4>
        <p class="text-muted mb-0" style="font-size:.78rem;">
            <i class="bi bi-calendar3 me-1"></i>
            Período: <span class="text-capitalize fw-semibold">{{ $fecha->translatedFormat('F Y') }}</span>
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('home', ['mes' => $anterior->month, 'anio' => $anterior->year]) }}"
           class="btn btn-sm btn-outline-secondary px-3">
            <i class="bi bi-chevron-left"></i>
        </a>
        <span class="fw-semibold px-1 text-capitalize" style="font-size:.9rem;min-width:130px;text-align:center;">
            {{ $fecha->translatedFormat('F Y') }}
        </span>
        <a href="{{ route('home', ['mes' => $siguiente->month, 'anio' => $siguiente->year]) }}"
           class="btn btn-sm btn-outline-secondary px-3">
            <i class="bi bi-chevron-right"></i>
        </a>
        @if(!$esMesActual)
        <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-calendar-check me-1"></i>Hoy
        </a>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════
     6 KPI CARDS
═══════════════════════════════════════════ --}}
@php $rutaProyectos = auth()->user()->can('ver-trabajo') ? 'proyectos.index' : 'proyectos.mis'; @endphp
<div class="row g-3 mb-4">

    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ route($rutaProyectos, ['estado_trabajo' => 'pendiente']) }}"
           class="kpi-card" style="--kpi-color:#fd7e14;">
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div>
                <span class="kpi-label">Pendientes</span>
            </div>
            <div class="kpi-value">{{ $pendientes }}</div>
        </a>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ route($rutaProyectos, ['estado_trabajo' => 'proceso']) }}"
           class="kpi-card" style="--kpi-color:#0d6efd;">
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="kpi-icon"><i class="bi bi-arrow-repeat"></i></div>
                <span class="kpi-label">En Proceso</span>
            </div>
            <div class="kpi-value">{{ $enProceso }}</div>
        </a>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ route($rutaProyectos, ['estado_trabajo' => 'terminado']) }}"
           class="kpi-card" style="--kpi-color:#198754;">
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="kpi-icon"><i class="bi bi-check2-circle"></i></div>
                <span class="kpi-label">Finalizados</span>
            </div>
            <div class="kpi-value">{{ $finalizados }}</div>
        </a>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ route($rutaProyectos, ['estado_trabajo' => 'cancelado']) }}"
           class="kpi-card" style="--kpi-color:#6c757d;">
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="kpi-icon"><i class="bi bi-x-circle"></i></div>
                <span class="kpi-label">Cancelados</span>
            </div>
            <div class="kpi-value">{{ $cancelados }}</div>
        </a>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ route($rutaProyectos, ['estado_trabajo' => 'proceso']) }}"
           class="kpi-card" style="--kpi-color:#dc3545;">
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="kpi-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <span class="kpi-label">Atrasados</span>
            </div>
            <div class="kpi-value {{ $atrasadosCnt > 0 ? 'alert-value' : '' }}">{{ $atrasadosCnt }}</div>
        </a>
    </div>

    @if($esAdmin)
    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ route('proyectos.index', ['responsable_id' => 'null']) }}"
           class="kpi-card" style="--kpi-color:#6f42c1;">
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="kpi-icon"><i class="bi bi-person-dash-fill"></i></div>
                <span class="kpi-label">Sin asignar</span>
            </div>
            <div class="kpi-value {{ $sinResponsableCnt > 0 ? 'alert-value' : '' }}">{{ $sinResponsableCnt }}</div>
        </a>
    </div>
    @else
    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ route($rutaProyectos) }}"
           class="kpi-card" style="--kpi-color:#20c997;">
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="kpi-icon"><i class="bi bi-clock-history"></i></div>
                <span class="kpi-label">Vencen en 7d</span>
            </div>
            <div class="kpi-value {{ $porVencer7Cnt > 0 ? 'alert-value' : '' }}">{{ $porVencer7Cnt }}</div>
        </a>
    </div>
    @endif

</div>

{{-- ══════════════════════════════════════════
     FILA VISUAL: GRÁFICO + MÉTRICAS
═══════════════════════════════════════════ --}}
@php
    $tasaTotal  = $finalizados + $cancelados;
    $tasaPct    = $tasaTotal > 0 ? round($finalizados / $tasaTotal * 100) : null;
    $tasaColor  = $tasaPct === null ? '#adb5bd' : ($tasaPct >= 70 ? '#198754' : ($tasaPct >= 40 ? '#fd7e14' : '#dc3545'));
    $atiempoPct = $finalizados > 0 ? round($completadosATiempoCnt / $finalizados * 100) : null;
    $totalChart = $pendientes + $enProceso + $finalizados + $cancelados;
@endphp
<div class="row g-3 mb-4">

    {{-- Donut: distribución del mes --}}
    <div class="col-lg-4">
        <div class="dash-card p-3 h-100">
            <p class="section-label mb-3"><i class="bi bi-pie-chart-fill me-1"></i>Distribución del mes</p>
            @if($totalChart > 0)
            <div style="position:relative;max-height:220px;display:flex;align-items:center;justify-content:center;">
                <canvas id="chartEstados"></canvas>
                <div style="position:absolute;text-align:center;pointer-events:none;">
                    <div class="fw-bold" style="font-size:1.6rem;line-height:1;">{{ $totalChart }}</div>
                    <div style="font-size:.65rem;color:#999;">proyectos</div>
                </div>
            </div>
            @else
            <div class="text-center text-muted py-4">
                <i class="bi bi-bar-chart d-block mb-1" style="font-size:2rem;"></i>
                Sin datos este mes
            </div>
            @endif
        </div>
    </div>

    {{-- Métricas + alertas --}}
    <div class="col-lg-8">
        <div class="row g-3 h-100">

            {{-- Tasa de cumplimiento --}}
            <div class="col-sm-6">
                <div class="dash-card p-3 h-100">
                    <p class="section-label"><i class="bi bi-graph-up me-1"></i>Tasa de cumplimiento</p>
                    @if($tasaPct !== null)
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="metric-track flex-grow-1">
                            <div class="metric-fill" style="width:{{ $tasaPct }}%;background:{{ $tasaColor }};"></div>
                        </div>
                        <span class="fw-bold" style="font-size:1.05rem;color:{{ $tasaColor }};min-width:40px;text-align:right;">{{ $tasaPct }}%</span>
                    </div>
                    <p style="font-size:.68rem;color:#999;margin:0;">
                        {{ $finalizados }} finalizados · {{ $cancelados }} cancelados
                    </p>
                    @else
                    <p class="text-muted small mb-0">Sin proyectos cerrados este mes</p>
                    @endif
                </div>
            </div>

            {{-- Completados a tiempo --}}
            <div class="col-sm-6">
                <div class="dash-card p-3 h-100">
                    <p class="section-label"><i class="bi bi-patch-check me-1"></i>Completados a tiempo</p>
                    @if($atiempoPct !== null)
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="metric-track flex-grow-1">
                            <div class="metric-fill" style="width:{{ $atiempoPct }}%;background:#198754;"></div>
                        </div>
                        <span class="fw-bold" style="font-size:1.05rem;color:#198754;min-width:40px;text-align:right;">{{ $atiempoPct }}%</span>
                    </div>
                    <p style="font-size:.68rem;color:#999;margin:0;">
                        {{ $completadosATiempoCnt }} de {{ $finalizados }} entregados en fecha
                    </p>
                    @else
                    <p class="text-muted small mb-0">Sin finalizados este mes</p>
                    @endif
                </div>
            </div>

            {{-- Por vencer --}}
            <div class="col-sm-6">
                <div class="dash-card p-3 h-100" style="border-left:4px solid {{ $porVencer7Cnt > 0 ? '#fd7e14' : '#dee2e6' }};">
                    <p class="section-label"><i class="bi bi-clock-history me-1"></i>Próximos a vencer</p>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold lh-1" style="font-size:2.2rem;color:{{ $porVencer7Cnt > 0 ? '#fd7e14' : '#ccc' }};">{{ $porVencer7Cnt }}</span>
                        <span style="font-size:.75rem;color:#888;">{{ $porVencer7Cnt === 1 ? 'proyecto vence' : 'proyectos vencen' }}<br>en los próximos 7 días</span>
                    </div>
                </div>
            </div>

            {{-- Atrasados alerta --}}
            <div class="col-sm-6">
                <div class="dash-card p-3 h-100" style="border-left:4px solid {{ $atrasadosCnt > 0 ? '#dc3545' : '#dee2e6' }};">
                    <p class="section-label"><i class="bi bi-exclamation-triangle me-1"></i>Ya vencidos</p>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold lh-1" style="font-size:2.2rem;color:{{ $atrasadosCnt > 0 ? '#dc3545' : '#ccc' }};">{{ $atrasadosCnt }}</span>
                        <span style="font-size:.75rem;color:#888;">{{ $atrasadosCnt === 1 ? 'proyecto atrasado' : 'proyectos atrasados' }}<br>requieren atención</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════
     DEPARTAMENTOS (solo admin)
═══════════════════════════════════════════ --}}
@if($esAdmin && $porDepartamento->isNotEmpty())
@php
    $deptColors = ['#0d6efd','#198754','#6f42c1','#0dcaf0','#fd7e14','#20c997','#dc3545','#6c757d','#d63384','#e67e22'];
    $iconMap2 = [
        'construct'=>'bi-bricks','obra'=>'bi-bricks','consult'=>'bi-briefcase-fill',
        'asesor'=>'bi-briefcase-fill','diseño'=>'bi-palette-fill','tecnolog'=>'bi-cpu-fill',
        'sistem'=>'bi-cpu-fill','finanz'=>'bi-cash-stack','contab'=>'bi-calculator-fill',
        'admin'=>'bi-building','gerenc'=>'bi-person-badge-fill','rrhh'=>'bi-people-fill',
        'recurso'=>'bi-people-fill','legal'=>'bi-shield-check','juridic'=>'bi-shield-check',
        'logis'=>'bi-truck','transport'=>'bi-truck','market'=>'bi-megaphone-fill',
        'ventas'=>'bi-graph-up-arrow','comerc'=>'bi-graph-up-arrow','compras'=>'bi-cart-fill',
        'almac'=>'bi-boxes','producc'=>'bi-gear-wide-connected','manufactur'=>'bi-gear-wide-connected',
        'calidad'=>'bi-patch-check-fill','seguridad'=>'bi-shield-fill-check',
        'salud'=>'bi-heart-pulse-fill','capacit'=>'bi-mortarboard-fill',
    ];
@endphp
<p class="section-label"><i class="bi bi-building me-1"></i>Proyectos por Departamento</p>
<div class="row g-3 mb-4">
    @foreach($porDepartamento as $depto)
    @php
        $total   = $depto->total_pendientes + $depto->total_proceso + $depto->total_terminados + $depto->total_cancelados;
        $nd      = mb_strtolower($depto->nombre_departamento);
        $dIcon   = 'bi-building';
        foreach ($iconMap2 as $kw => $ic) { if (str_contains($nd, $kw)) { $dIcon = $ic; break; } }
        $dColor  = $deptColors[$loop->index % count($deptColors)];
        $modalId = 'modalDepto'.$depto->id;
    @endphp
    <div class="col-sm-6 col-md-4 col-xl-3">
        <div class="depto-card p-3 h-100" style="--d-color:{{ $dColor }};"
             data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:38px;height:38px;background:color-mix(in srgb,{{ $dColor }} 12%,transparent);">
                    <i class="bi {{ $dIcon }}" style="font-size:1.1rem;color:{{ $dColor }};"></i>
                </div>
                <div class="overflow-hidden">
                    <p class="fw-semibold mb-0 text-truncate" style="font-size:.85rem;">{{ $depto->nombre_departamento }}</p>
                    <p class="mb-0" style="font-size:.67rem;color:#aaa;">{{ $total }} {{ $total === 1 ? 'proyecto' : 'proyectos' }}</p>
                </div>
            </div>
            <div class="d-flex gap-1 flex-wrap">
                @if($total === 0)
                <span style="font-size:.62rem;color:#ccc;">Sin proyectos</span>
                @else
                    @if($depto->total_pendientes)
                    <span style="font-size:.62rem;background:#fff3e0;color:#fd7e14;padding:2px 7px;border-radius:20px;font-weight:600;">
                        <i class="bi bi-hourglass-split me-1"></i>{{ $depto->total_pendientes }}
                    </span>
                    @endif
                    @if($depto->total_proceso)
                    <span style="font-size:.62rem;background:#e8f0fe;color:#0d6efd;padding:2px 7px;border-radius:20px;font-weight:600;">
                        <i class="bi bi-arrow-repeat me-1"></i>{{ $depto->total_proceso }}
                    </span>
                    @endif
                    @if($depto->total_terminados)
                    <span style="font-size:.62rem;background:#e8f5e9;color:#198754;padding:2px 7px;border-radius:20px;font-weight:600;">
                        <i class="bi bi-check2-circle me-1"></i>{{ $depto->total_terminados }}
                    </span>
                    @endif
                    @if($depto->total_cancelados)
                    <span style="font-size:.62rem;background:#fdecea;color:#dc3545;padding:2px 7px;border-radius:20px;font-weight:600;">
                        <i class="bi bi-x-circle me-1"></i>{{ $depto->total_cancelados }}
                    </span>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header py-2 px-3" style="background:linear-gradient(90deg,#1a1a1a,#2d2d2d);">
                    <h6 class="modal-title text-white fw-semibold mb-0">
                        <i class="bi {{ $dIcon }} me-2" style="color:{{ $dColor }};"></i>{{ $depto->nombre_departamento }}
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <table class="table table-sm table-hover align-middle mb-0" style="font-size:.85rem;">
                        <thead>
                            <tr>
                                <th class="text-center" style="color:#fd7e14;"><i class="bi bi-hourglass-split me-1"></i>Pendientes</th>
                                <th class="text-center" style="color:#0d6efd;"><i class="bi bi-arrow-repeat me-1"></i>En Proceso</th>
                                <th class="text-center" style="color:#198754;"><i class="bi bi-check2-circle me-1"></i>Terminados</th>
                                <th class="text-center" style="color:#dc3545;"><i class="bi bi-x-circle me-1"></i>Cancelados</th>
                                <th class="text-center text-muted">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">
                                    @if($depto->total_pendientes > 0)
                                    <a href="{{ route('proyectos.index', ['departamento_id'=>$depto->id,'estado_trabajo'=>'pendiente']) }}" class="fw-bold text-decoration-none" style="color:#fd7e14;">{{ $depto->total_pendientes }}</a>
                                    @else <span class="text-muted">—</span> @endif
                                </td>
                                <td class="text-center">
                                    @if($depto->total_proceso > 0)
                                    <a href="{{ route('proyectos.index', ['departamento_id'=>$depto->id,'estado_trabajo'=>'proceso']) }}" class="fw-bold text-decoration-none" style="color:#0d6efd;">{{ $depto->total_proceso }}</a>
                                    @else <span class="text-muted">—</span> @endif
                                </td>
                                <td class="text-center">
                                    @if($depto->total_terminados > 0)
                                    <a href="{{ route('proyectos.index', ['departamento_id'=>$depto->id,'estado_trabajo'=>'terminado']) }}" class="fw-bold text-decoration-none" style="color:#198754;">{{ $depto->total_terminados }}</a>
                                    @else <span class="text-muted">—</span> @endif
                                </td>
                                <td class="text-center">
                                    @if($depto->total_cancelados > 0)
                                    <a href="{{ route('proyectos.index', ['departamento_id'=>$depto->id,'estado_trabajo'=>'cancelado']) }}" class="fw-bold text-decoration-none" style="color:#dc3545;">{{ $depto->total_cancelados }}</a>
                                    @else <span class="text-muted">—</span> @endif
                                </td>
                                <td class="text-center fw-bold text-muted">{{ $total }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer py-2 px-3 gap-2">
                    <a href="{{ route('proyectos.index', ['departamento_id' => $depto->id]) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Ver todos
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ══════════════════════════════════════════
     TIMELINE DE PROYECTOS ACTIVOS
═══════════════════════════════════════════ --}}
<p class="section-label"><i class="bi bi-bar-chart-steps me-1"></i>Proyectos Activos — Línea de Tiempo</p>
<div class="dash-card p-3 mb-3">
    @forelse($activos as $proyecto)
    @php
        $hoy       = now()->startOfDay();
        $pInicio   = $proyecto->fecha_inicio?->copy()->startOfDay();
        $pEstimada = $proyecto->fecha_estimada?->copy()->startOfDay();
        $pFin      = $proyecto->fecha_fin?->copy()->startOfDay();

        $pFinal  = $pEstimada && $pEstimada->gt($hoy) ? $pEstimada : $hoy;
        $pTotal  = max($pInicio ? $pInicio->diffInDays($pFinal) : 1, 1);
        $pHoyPos = $pInicio ? min(max(round($pInicio->diffInDays($hoy) / $pTotal * 100), 0), 100) : 0;

        if ($proyecto->estado_trabajo === 'terminado' && $pFin && $pInicio) {
            $pFill = min(round($pInicio->diffInDays($pFin) / $pTotal * 100), 100);
        } elseif ($pInicio) {
            $pFill = $pHoyPos;
        } else {
            $pFill = 0;
        }

        $diasParaVencer = $pEstimada ? $hoy->diffInDays($pEstimada, false) : null;
        $pAtrasado  = $pEstimada && $hoy->gt($pEstimada) && $proyecto->estado_trabajo !== 'terminado';
        $pPorVencer = ! $pAtrasado && $diasParaVencer !== null && $diasParaVencer <= 5 && $proyecto->estado_trabajo !== 'terminado';

        if ($proyecto->estado_trabajo === 'terminado') {
            $pColor = ($pFin && $pEstimada && $pFin->lte($pEstimada)) ? '#198754' : '#fd7e14';
        } elseif ($proyecto->estado_trabajo === 'pendiente') {
            $pColor = '#adb5bd';
        } elseif ($pAtrasado || $pPorVencer) {
            $pColor = '#dc3545';
        } else {
            $pColor = '#0d6efd';
        }

        $tlClass     = $pAtrasado ? 'tl-atrasado' : ($pPorVencer ? 'tl-porvencer' : ($proyecto->estado_trabajo === 'proceso' ? 'tl-proceso' : 'tl-pendiente'));
        $grupoActual = $pAtrasado ? 'atrasado'    : ($pPorVencer ? 'porvencer'    : $proyecto->estado_trabajo);
    @endphp

    @if($loop->first || $grupoActual !== ($grupoAnterior ?? null))
    @php
        $gl = match($grupoActual) {
            'atrasado'  => ['color'=>'#dc3545','icon'=>'bi-exclamation-triangle-fill','text'=>'Atrasados'],
            'porvencer' => ['color'=>'#fd7e14','icon'=>'bi-clock-fill',               'text'=>'Por vencer pronto'],
            'proceso'   => ['color'=>'#0d6efd','icon'=>'bi-arrow-repeat',             'text'=>'En proceso'],
            default     => ['color'=>'#adb5bd','icon'=>'bi-hourglass-split',          'text'=>'Pendientes'],
        };
    @endphp
    <div class="grupo-sep" style="--g-color:{{ $gl['color'] }};">
        <i class="bi {{ $gl['icon'] }}"></i> {{ $gl['text'] }}
    </div>
    @endif
    @php $grupoAnterior = $grupoActual; @endphp

    <div class="tl-row {{ $tlClass }}">
        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
            <a href="{{ route('proyectos.show', $proyecto->id) }}"
               class="fw-semibold small text-decoration-none text-dark">
                #{{ $proyecto->id }} — {{ $proyecto->cliente->nombre_completo }}
            </a>
            <span class="text-muted small">· {{ $proyecto->servicio->nombre_servicio }}</span>
            <span class="badge bg-{{ $proyecto->estado_badge }}" style="font-size:.6rem;">{{ $proyecto->estado_label }}</span>
            @if($proyecto->trabajo_unico)
            <span class="badge" style="background:#f0f0f0;color:#555;font-size:.58rem;border:1px solid #ddd;">
                <i class="bi bi-1-circle me-1"></i>Único
            </span>
            @endif
            @if($pAtrasado)
            <span class="badge bg-danger" style="font-size:.58rem;">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>Atrasado {{ abs($diasParaVencer) }}d
            </span>
            @elseif($pPorVencer)
            <span class="badge bg-danger" style="font-size:.58rem;">
                <i class="bi bi-clock me-1"></i>Vence en {{ $diasParaVencer }}d
            </span>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="position-relative flex-grow-1" style="height:3px;">
                <div style="height:100%;background:#e0e0e0;border-radius:6px;overflow:hidden;">
                    <div style="height:100%;width:{{ max($pFill,1) }}%;background:{{ $pColor }};border-radius:6px;transition:width .5s ease;"></div>
                </div>
                @if($proyecto->estado_trabajo !== 'terminado')
                <div style="position:absolute;left:{{ $pHoyPos }}%;top:-2px;width:7px;height:7px;background:#fff;border:2px solid #333;border-radius:50%;z-index:2;transform:translateX(-50%);"
                     title="Hoy: {{ $hoy->format('d/m/Y') }}"></div>
                @endif
            </div>
            <span style="font-size:.6rem;color:{{ $pColor }};font-weight:700;min-width:30px;text-align:right;">{{ $pFill }}%</span>
        </div>
        <div class="d-flex justify-content-between mt-1" style="font-size:.63rem;color:#aaa;">
            <span>{{ $pInicio?->format('d/m/Y') ?? '—' }}</span>
            @if($pFin && $proyecto->estado_trabajo === 'terminado')
            <span class="fw-semibold" style="color:{{ $pColor }};">Fin: {{ $pFin->format('d/m/Y') }}</span>
            @endif
            <span>Est: {{ $pEstimada?->format('d/m/Y') ?? '—' }}</span>
        </div>

        @if(! $proyecto->trabajo_unico && $proyecto->subtrabajos->isNotEmpty())
        <div class="ms-3 mt-2 border-start ps-3" style="border-color:#e8e8e8 !important;">
            @foreach($proyecto->subtrabajos as $sub)
            @php
                $sInicio   = $sub->fecha_inicio?->copy()->startOfDay();
                $sEstimada = $sub->fecha_estimada?->copy()->startOfDay();
                $sFin      = $sub->fecha_fin?->copy()->startOfDay();
                $sFinal    = $sEstimada && $sEstimada->gt($hoy) ? $sEstimada : $hoy;
                $sTotal    = max($sInicio ? $sInicio->diffInDays($sFinal) : 1, 1);
                $sHoyPos   = $sInicio ? min(max(round($sInicio->diffInDays($hoy) / $sTotal * 100), 0), 100) : 0;
                if ($sub->estado === 'terminado' && $sFin && $sInicio) {
                    $sFill = min(round($sInicio->diffInDays($sFin) / $sTotal * 100), 100);
                } elseif ($sInicio) { $sFill = $sHoyPos; } else { $sFill = 0; }
                $sDias     = $sEstimada ? $hoy->diffInDays($sEstimada, false) : null;
                $sAtrasado = $sEstimada && $hoy->gt($sEstimada) && $sub->estado !== 'terminado';
                $sPorVencer= !$sAtrasado && $sDias !== null && $sDias <= 5 && $sub->estado !== 'terminado';
                if ($sub->estado === 'terminado') {
                    $sColor = ($sFin && $sEstimada && $sFin->lte($sEstimada)) ? '#198754' : '#fd7e14';
                } elseif ($sub->estado === 'pendiente') { $sColor = '#adb5bd';
                } elseif ($sAtrasado || $sPorVencer) { $sColor = '#dc3545';
                } else { $sColor = '#0d6efd'; }
            @endphp
            <div class="mb-2">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <a href="{{ route('subtrabajos.show', [$proyecto->id, $sub->id]) }}"
                       class="small text-muted text-decoration-none">
                        <i class="bi bi-diagram-3 me-1"></i>Sub #{{ $sub->id }}
                        @if($sub->servicio) — {{ $sub->servicio->nombre_servicio }} @endif
                    </a>
                    <span class="badge bg-{{ $sub->estado_badge }}" style="font-size:.57rem;">{{ $sub->estado_label }}</span>
                    @if($sAtrasado)<span class="badge bg-danger" style="font-size:.57rem;">Atrasado</span>
                    @elseif($sPorVencer)<span class="badge bg-danger" style="font-size:.57rem;">Vence {{ $sDias }}d</span>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="position-relative flex-grow-1" style="height:2px;">
                        <div style="height:100%;background:#e0e0e0;border-radius:4px;overflow:hidden;">
                            <div style="height:100%;width:{{ max($sFill,1) }}%;background:{{ $sColor }};border-radius:4px;transition:width .5s ease;"></div>
                        </div>
                        @if($sub->estado !== 'terminado')
                        <div style="position:absolute;left:{{ $sHoyPos }}%;top:-2px;width:6px;height:6px;background:#fff;border:1.5px solid #555;border-radius:50%;z-index:2;transform:translateX(-50%);" title="Hoy"></div>
                        @endif
                    </div>
                    <span style="font-size:.58rem;color:{{ $sColor }};font-weight:700;min-width:30px;text-align:right;">{{ $sFill }}%</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    @empty
    <div class="text-center text-muted py-5">
        <i class="bi bi-check2-all d-block mb-2" style="font-size:2.5rem;opacity:.3;"></i>
        <span class="small">No hay proyectos activos en este momento.</span>
    </div>
    @endforelse
</div>

{{-- Mis Subtrabajos --}}
@if(! $esAdmin && $misSubtrabajos->isNotEmpty())
<p class="section-label"><i class="bi bi-diagram-3 me-1"></i>Mis Subtrabajos — Línea de Tiempo</p>
<div class="dash-card p-3">
    @foreach($misSubtrabajos as $sub)
    @php
        $hoy       = now()->startOfDay();
        $sInicio   = $sub->fecha_inicio?->copy()->startOfDay();
        $sEstimada = $sub->fecha_estimada?->copy()->startOfDay();
        $sFin      = $sub->fecha_fin?->copy()->startOfDay();
        $sFinal    = $sEstimada && $sEstimada->gt($hoy) ? $sEstimada : $hoy;
        $sTotal    = max($sInicio ? $sInicio->diffInDays($sFinal) : 1, 1);
        $sHoyPos   = $sInicio ? min(max(round($sInicio->diffInDays($hoy) / $sTotal * 100), 0), 100) : 0;
        if ($sub->estado === 'terminado' && $sFin && $sInicio) {
            $sFill = min(round($sInicio->diffInDays($sFin) / $sTotal * 100), 100);
        } elseif ($sInicio) { $sFill = $sHoyPos; } else { $sFill = 0; }
        $sDias     = $sEstimada ? $hoy->diffInDays($sEstimada, false) : null;
        $sAtrasado = $sEstimada && $hoy->gt($sEstimada) && $sub->estado !== 'terminado';
        $sPorVencer= !$sAtrasado && $sDias !== null && $sDias <= 5 && $sub->estado !== 'terminado';
        if ($sub->estado === 'terminado') {
            $sColor = ($sFin && $sEstimada && $sFin->lte($sEstimada)) ? '#198754' : '#fd7e14';
        } elseif ($sub->estado === 'pendiente') { $sColor = '#adb5bd';
        } elseif ($sAtrasado || $sPorVencer) { $sColor = '#dc3545';
        } else { $sColor = '#0d6efd'; }
        $sTlClass = $sAtrasado ? 'tl-atrasado' : ($sPorVencer ? 'tl-porvencer' : ($sub->estado === 'proceso' ? 'tl-proceso' : 'tl-pendiente'));
    @endphp
    <div class="tl-row {{ $sTlClass }}">
        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
            <a href="{{ route('subtrabajos.show', [$sub->trabajo_id, $sub->id]) }}"
               class="fw-semibold small text-decoration-none text-dark">
                Sub #{{ $sub->id }}@if($sub->servicio) — {{ $sub->servicio->nombre_servicio }}@endif
            </a>
            <span class="text-muted small">· Proy. #{{ $sub->trabajo_id }}</span>
            <span class="badge bg-{{ $sub->estado_badge }}" style="font-size:.6rem;">{{ $sub->estado_label }}</span>
            @if($sAtrasado)<span class="badge bg-danger" style="font-size:.58rem;">Atrasado</span>
            @elseif($sPorVencer)<span class="badge bg-danger" style="font-size:.58rem;">Vence {{ $sDias }}d</span>@endif
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="position-relative flex-grow-1" style="height:3px;">
                <div style="height:100%;background:#e0e0e0;border-radius:6px;overflow:hidden;">
                    <div style="height:100%;width:{{ max($sFill,1) }}%;background:{{ $sColor }};border-radius:6px;transition:width .5s ease;"></div>
                </div>
                @if($sub->estado !== 'terminado')
                <div style="position:absolute;left:{{ $sHoyPos }}%;top:-2px;width:7px;height:7px;background:#fff;border:2px solid #333;border-radius:50%;z-index:2;transform:translateX(-50%);" title="Hoy: {{ $hoy->format('d/m/Y') }}"></div>
                @endif
            </div>
            <span style="font-size:.6rem;color:{{ $sColor }};font-weight:700;min-width:30px;text-align:right;">{{ $sFill }}%</span>
        </div>
        <div class="d-flex justify-content-between mt-1" style="font-size:.63rem;color:#aaa;">
            <span>{{ $sInicio?->format('d/m/Y') ?? '—' }}</span>
            @if($sFin && $sub->estado === 'terminado')<span style="color:{{ $sColor }};">Fin: {{ $sFin->format('d/m/Y') }}</span>@endif
            <span>Est: {{ $sEstimada?->format('d/m/Y') ?? '—' }}</span>
        </div>
    </div>
    @endforeach
</div>
@endif

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
@if($totalChart > 0)
(function() {
    const ctx = document.getElementById('chartEstados');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pendientes', 'En Proceso', 'Finalizados', 'Cancelados'],
            datasets: [{
                data: [{{ $pendientes }}, {{ $enProceso }}, {{ $finalizados }}, {{ $cancelados }}],
                backgroundColor: ['#fd7e14','#0d6efd','#198754','#6c757d'],
                borderWidth: 3,
                borderColor: '#fff',
                hoverBorderWidth: 3,
            }]
        },
        options: {
            responsive: true,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 14, font: { size: 11 }, usePointStyle: true, pointStyleWidth: 8 }
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ` ${ctx.label}: ${ctx.parsed} (${Math.round(ctx.parsed/{{ $totalChart }}*100)}%)`
                    }
                }
            }
        }
    });
})();
@endif
</script>
@endpush
@endsection
