@extends('layouts.app')

@push('styles')
<style>
    #mainContent { background: #f0f2f5; }

    .dash-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
    }

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
    .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.11); color: inherit; }
    .kpi-icon {
        width: 38px; height: 38px; border-radius: 50%;
        background: color-mix(in srgb, var(--kpi-color) 12%, transparent);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .kpi-icon i { font-size: 1rem; color: var(--kpi-color); }
    .kpi-label { font-size: .65rem; color: #999; margin-bottom: 1px; }
    .kpi-value { font-size: 1.55rem; font-weight: 700; line-height: 1; color: #1a1a1a; }

    .comision-card {
        border-radius: 10px;
        padding: 18px 20px;
        color: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,.12);
    }
    .comision-card .label { font-size: .7rem; opacity: .8; text-transform: uppercase; letter-spacing: .08em; }
    .comision-card .monto { font-size: 1.9rem; font-weight: 700; line-height: 1.1; }
    .comision-card .sub   { font-size: .72rem; opacity: .75; margin-top: 4px; }

    .tl-row { padding: 10px 14px; border-radius: 8px; margin-bottom: 8px; border-left: 3px solid transparent; }
    .tl-atrasado  { background:#fff5f5; border-left-color:#dc3545; }
    .tl-porvencer { background:#fffbf3; border-left-color:#fd7e14; }
    .tl-proceso   { background:#f7fbff; border-left-color:#0d6efd; }
    .tl-pendiente { background:#fafafa; border-left-color:#dee2e6; }

    .section-label {
        font-size: .68rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .08em; color: #888; margin-bottom: 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

{{-- ── HEADER ── --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="font-size:1.25rem;">Mi Dashboard de Ventas</h4>
        <p class="text-muted mb-0" style="font-size:.78rem;">
            <i class="bi bi-graph-up-arrow me-1"></i>
            {{ auth()->user()->nombre }} {{ auth()->user()->apellido }}
        </p>
    </div>
    <a href="{{ route('proyectos.ventas') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-list-ul me-1"></i> Ver todos mis proyectos
    </a>
</div>

{{-- ── KPI CARDS ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="{{ route('proyectos.ventas', ['estado_trabajo' => 'pendiente']) }}" class="kpi-card" style="--kpi-color:#fd7e14;">
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div>
                <span class="kpi-label">Pendientes</span>
            </div>
            <div class="kpi-value">{{ $pendientes }}</div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('proyectos.ventas', ['estado_trabajo' => 'proceso']) }}" class="kpi-card" style="--kpi-color:#0d6efd;">
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="kpi-icon"><i class="bi bi-arrow-repeat"></i></div>
                <span class="kpi-label">En Proceso</span>
            </div>
            <div class="kpi-value">{{ $enProceso }}</div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('proyectos.ventas', ['estado_trabajo' => 'terminado']) }}" class="kpi-card" style="--kpi-color:#198754;">
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="kpi-icon"><i class="bi bi-check2-circle"></i></div>
                <span class="kpi-label">Terminados</span>
            </div>
            <div class="kpi-value">{{ $terminados }}</div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('proyectos.ventas', ['estado_trabajo' => 'cancelado']) }}" class="kpi-card" style="--kpi-color:#6c757d;">
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="kpi-icon"><i class="bi bi-x-circle"></i></div>
                <span class="kpi-label">Cancelados</span>
            </div>
            <div class="kpi-value">{{ $cancelados }}</div>
        </a>
    </div>
</div>

{{-- ── COMISIONES ── --}}
<p class="section-label"><i class="bi bi-cash-coin me-1"></i>Mis Comisiones</p>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="comision-card" style="background: linear-gradient(135deg, #198754 0%, #20c997 100%);">
            <div class="label"><i class="bi bi-check2-circle me-1"></i>Comisión Ganada</div>
            <div class="monto">${{ number_format($comisionGanada, 2) }}</div>
            <div class="sub">De proyectos terminados con comisión asignada</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="comision-card" style="background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);">
            <div class="label"><i class="bi bi-hourglass-split me-1"></i>Comisión Proyectada</div>
            <div class="monto">${{ number_format($comisionPendiente, 2) }}</div>
            <div class="sub">De proyectos activos pendientes de cierre</div>
        </div>
    </div>
</div>

{{-- ── PROYECTOS ACTIVOS ── --}}
<p class="section-label"><i class="bi bi-bar-chart-steps me-1"></i>Mis Proyectos Activos</p>
<div class="dash-card p-3 mb-3">
    @forelse($activos as $proyecto)
    @php
        $pInicio   = $proyecto->fecha_inicio?->copy()->startOfDay();
        $pEstimada = $proyecto->fecha_estimada?->copy()->startOfDay();
        $pFinal    = $pEstimada && $pEstimada->gt($hoy) ? $pEstimada : $hoy;
        $pTotal    = max($pInicio ? $pInicio->diffInDays($pFinal) : 1, 1);
        $pHoyPos   = $pInicio ? min(max(round($pInicio->diffInDays($hoy) / $pTotal * 100), 0), 100) : 0;
        $pFill     = $pInicio ? $pHoyPos : 0;

        $diasParaVencer = $pEstimada ? $hoy->diffInDays($pEstimada, false) : null;
        $pAtrasado      = $pEstimada && $hoy->gt($pEstimada) && $proyecto->estado_trabajo !== 'terminado';
        $pPorVencer     = ! $pAtrasado && $diasParaVencer !== null && $diasParaVencer <= 5;

        $pColor  = $pAtrasado ? '#dc3545' : ($pPorVencer ? '#fd7e14' : ($proyecto->estado_trabajo === 'proceso' ? '#0d6efd' : '#adb5bd'));
        $tlClass = $pAtrasado ? 'tl-atrasado' : ($pPorVencer ? 'tl-porvencer' : ($proyecto->estado_trabajo === 'proceso' ? 'tl-proceso' : 'tl-pendiente'));

        $total      = $proyecto->subtrabajos->count();
        $terminados = $proyecto->subtrabajos->where('estado', 'terminado')->count();
        $pctSubs    = $total > 0 ? round($terminados / $total * 100) : 0;
    @endphp
    <div class="tl-row {{ $tlClass }}">
        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
            <span class="fw-semibold small text-dark">
                #{{ $proyecto->id }} — {{ $proyecto->cliente->nombre_completo }}
            </span>
            <span class="text-muted small">· {{ $proyecto->servicio->nombre_servicio }}</span>
            <span class="badge bg-{{ $proyecto->estado_badge }}" style="font-size:.6rem;">{{ $proyecto->estado_label }}</span>
            @if($pAtrasado)
            <span class="badge bg-danger" style="font-size:.58rem;"><i class="bi bi-exclamation-triangle-fill me-1"></i>Atrasado {{ abs($diasParaVencer) }}d</span>
            @elseif($pPorVencer)
            <span class="badge bg-warning text-dark" style="font-size:.58rem;"><i class="bi bi-clock me-1"></i>Vence en {{ $diasParaVencer }}d</span>
            @endif
        </div>

        {{-- Progreso subtrabajos --}}
        @if(! $proyecto->trabajo_unico && $total > 0)
        <div class="d-flex align-items-center gap-2 mb-1">
            <div class="flex-grow-1" style="height:6px;background:#e0e0e0;border-radius:4px;overflow:hidden;">
                <div style="height:100%;width:{{ $pctSubs }}%;background:{{ $pColor }};border-radius:4px;transition:width .5s ease;"></div>
            </div>
            <span style="font-size:.6rem;color:{{ $pColor }};font-weight:700;min-width:50px;text-align:right;">{{ $terminados }}/{{ $total }} sub</span>
        </div>
        @endif

        {{-- Datos económicos --}}
        <div class="d-flex justify-content-between align-items-center mt-1" style="font-size:.68rem;">
            <span class="text-muted">
                <i class="bi bi-calendar3 me-1"></i>
                Est: {{ $pEstimada?->format('d/m/Y') ?? '—' }}
            </span>
            <span class="d-flex align-items-center gap-2">
                <span class="text-muted">Monto: <strong class="text-dark">${{ number_format($proyecto->monto_total, 2) }}</strong></span>
                @if($proyecto->porcentaje_comision !== null)
                <span class="badge bg-success" style="font-size:.6rem;">
                    {{ $proyecto->porcentaje_comision }}% · ${{ number_format($proyecto->monto_comision, 2) }}
                </span>
                @else
                <span class="text-muted" style="font-size:.6rem;">Sin comisión asignada</span>
                @endif
            </span>
        </div>
    </div>
    @empty
    <div class="text-center text-muted py-5">
        <i class="bi bi-graph-up d-block mb-2" style="font-size:2.5rem;opacity:.3;"></i>
        <span class="small">No tienes proyectos activos en este momento.</span>
    </div>
    @endforelse
</div>

</div>
@endsection
