@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">
            <i class="bi bi-cash-stack title-icon"></i> Cuentas por Cobrar
        </h4>
    </div>

    {{-- KPIs --}}
    <div class="row g-3 mb-4">

        {{-- Total Pendiente --}}
        <div class="col-6 col-xl-3">
            <a href="{{ route('cobros.index', array_merge(request()->query(), ['estado_pago' => 'con_saldo'])) }}"
               class="d-block text-decoration-none bg-white rounded shadow-sm p-3"
               style="border-left:4px solid #0d6efd;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div style="font-size:.7rem;color:#999;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Total Pendiente</div>
                        <div style="font-size:1.5rem;font-weight:400;color:#0d6efd;line-height:1.1;">${{ number_format($kpiPendiente->importe, 2) }}</div>
                        <div style="font-size:.75rem;color:#aaa;">{{ $kpiPendiente->cnt }} {{ $kpiPendiente->cnt == 1 ? 'cuenta' : 'cuentas' }}</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:42px;height:42px;background:#e8f0fe;">
                        <i class="bi bi-wallet2" style="font-size:1.1rem;color:#0d6efd;"></i>
                    </div>
                </div>
            </a>
        </div>

        {{-- Vencidas --}}
        <div class="col-6 col-xl-3">
            <a href="{{ route('cobros.index', array_merge(request()->query(), ['vencimiento' => 'vencidas'])) }}"
               class="d-block text-decoration-none bg-white rounded shadow-sm p-3"
               style="border-left:4px solid #dc3545;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div style="font-size:.7rem;color:#999;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Vencidas</div>
                        <div style="font-size:1.5rem;font-weight:400;color:{{ $kpiVencidas->importe > 0 ? '#dc3545' : '#1a1a1a' }};line-height:1.1;">${{ number_format($kpiVencidas->importe, 2) }}</div>
                        <div style="font-size:.75rem;color:#aaa;">{{ $kpiVencidas->cnt }} {{ $kpiVencidas->cnt == 1 ? 'cuenta' : 'cuentas' }}</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:42px;height:42px;background:#fdecea;">
                        <i class="bi bi-exclamation-triangle" style="font-size:1.1rem;color:#dc3545;"></i>
                    </div>
                </div>
            </a>
        </div>

        {{-- Por Vencer 7 días --}}
        <div class="col-6 col-xl-3">
            <a href="{{ route('cobros.index', array_merge(request()->query(), ['vencimiento' => 'proximas'])) }}"
               class="d-block text-decoration-none bg-white rounded shadow-sm p-3"
               style="border-left:4px solid #6c757d;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div style="font-size:.7rem;color:#999;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Por Vencer (7 días)</div>
                        <div style="font-size:1.5rem;font-weight:400;color:#1a1a1a;line-height:1.1;">${{ number_format($kpiPorVencer->importe, 2) }}</div>
                        <div style="font-size:.75rem;color:#aaa;">Próximas a vencer</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:42px;height:42px;background:#f0f0f0;">
                        <i class="bi bi-clock-history" style="font-size:1.1rem;color:#6c757d;"></i>
                    </div>
                </div>
            </a>
        </div>

        {{-- Tasa de Cobranza --}}
        <div class="col-6 col-xl-3">
            <div class="bg-white rounded shadow-sm p-3" style="border-left:4px solid #198754;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div style="font-size:.7rem;color:#999;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Tasa de Cobranza</div>
                        <div style="font-size:1.5rem;font-weight:400;color:#198754;line-height:1.1;">
                            {{ $tasaPct !== null ? $tasaPct.'%' : '—' }}
                        </div>
                        <div style="font-size:.75rem;color:#aaa;">Porcentaje cobrado</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:42px;height:42px;background:#e6f4ec;">
                        <i class="bi bi-graph-up-arrow" style="font-size:1.1rem;color:#198754;"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('cobros.index') }}" class="bg-white rounded shadow-sm p-3 mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1 small">Buscar cliente</label>
                <input type="text" name="buscar" class="form-control form-control-sm"
                       placeholder="Nombre, cédula, razón social..." value="{{ request('buscar') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small">Estado de pago</label>
                <select name="estado_pago" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="con_saldo" {{ request('estado_pago') === 'con_saldo' ? 'selected' : '' }}>Con Saldo</option>
                    <option value="pendiente" {{ request('estado_pago') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="parcial"   {{ request('estado_pago') === 'parcial'   ? 'selected' : '' }}>Parcial</option>
                    <option value="pagado"    {{ request('estado_pago') === 'pagado'    ? 'selected' : '' }}>Pagado</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small">Vencimiento</label>
                <select name="vencimiento" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="vencidas" {{ request('vencimiento') === 'vencidas' ? 'selected' : '' }}>Vencidas</option>
                    <option value="proximas" {{ request('vencimiento') === 'proximas' ? 'selected' : '' }}>Próximas 7 días</option>
                    <option value="sin_fecha"{{ request('vencimiento') === 'sin_fecha'? 'selected' : '' }}>Sin fecha</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="{{ route('cobros.index') }}" class="btn btn-outline-secondary btn-sm w-100" title="Limpiar">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </div>
    </form>

    {{-- Tabla --}}
    <div class="bg-white rounded shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 small">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Proyecto</th>
                        <th>Creado</th>
                        <th>Est. Proyecto</th>
                        <th class="text-end">Monto Total</th>
                        <th class="text-end">Saldo</th>
                        <th>Progreso</th>
                        <th>Est. Pago</th>
                        <th>Vencimiento</th>
                        <th class="text-center" style="width:80px;">Ver</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cuentas as $cuenta)
                    @php
                        $vencida = $cuenta->fecha_vencimiento && $cuenta->fecha_vencimiento->isPast() && $cuenta->estado_pago !== 'pagado' && $cuenta->trabajo->estado_trabajo !== 'cancelado';
                    @endphp
                    <tr class="{{ $vencida ? 'table-danger' : '' }}">
                        <td class="text-muted">{{ $cuenta->id }}</td>
                        <td>
                            <p class="mb-0 fw-semibold">{{ $cuenta->trabajo->cliente->nombre_completo }}</p>
                            <p class="mb-0 text-muted" style="font-size:.72rem;">{{ $cuenta->trabajo->cliente->identificacion_clientes }}</p>
                        </td>
                        <td>
                            <p class="mb-0">{{ $cuenta->trabajo->servicio?->nombre_servicio ?? '—' }}</p>
                            <p class="mb-0 text-muted" style="font-size:.72rem;">{{ $cuenta->trabajo->departamento?->nombre_departamento ?? '—' }}</p>
                        </td>
                        <td class="text-muted" style="font-size:.8rem;">{{ $cuenta->created_at->format('d/m/Y') }}</td>
                        <td>
                            @php
                                $estadoBadge = match($cuenta->trabajo->estado_trabajo) {
                                    'pendiente' => 'info',
                                    'proceso'   => 'primary',
                                    'terminado' => 'success',
                                    'cancelado' => 'danger',
                                    default     => 'secondary',
                                };
                                $estadoLabel = match($cuenta->trabajo->estado_trabajo) {
                                    'pendiente' => 'Pendiente',
                                    'proceso'   => 'En Proceso',
                                    'terminado' => 'Terminado',
                                    'cancelado' => 'Cancelado',
                                    default     => ucfirst($cuenta->trabajo->estado_trabajo ?? '—'),
                                };
                            @endphp
                            <span class="badge bg-{{ $estadoBadge }}" style="font-size:.7rem;">{{ $estadoLabel }}</span>
                        </td>
                        <td class="text-end fw-semibold">${{ number_format($cuenta->monto_total, 2) }}</td>
                        <td class="text-end {{ $cuenta->saldo > 0 ? 'text-danger fw-semibold' : 'text-success' }}">
                            ${{ number_format($cuenta->saldo, 2) }}
                        </td>
                        <td style="min-width:100px;">
                            <div class="progress" style="height:6px;">
                                <div class="progress-bar bg-success"
                                     style="width:{{ $cuenta->porcentaje_pagado }}%"></div>
                            </div>
                            <span style="font-size:.68rem;color:#888;">{{ $cuenta->porcentaje_pagado }}%</span>
                        </td>
                        <td>
                            @if($cuenta->trabajo->estado_trabajo === 'cancelado')
                                <span class="badge bg-danger">Suspendida</span>
                            @else
                                <span class="badge bg-{{ $cuenta->estado_pago_badge }}">
                                    {{ $cuenta->estado_pago_label }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($cuenta->fecha_vencimiento)
                                <span class="{{ $vencida ? 'text-danger fw-semibold' : 'text-muted' }}" style="font-size:.8rem;">
                                    {{ $cuenta->fecha_vencimiento->format('d/m/Y') }}
                                    @if($vencida) <i class="bi bi-exclamation-triangle-fill ms-1"></i> @endif
                                </span>
                            @else
                                <span class="text-muted" style="font-size:.8rem;">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('cobros.show', $cuenta->id) }}"
                               class="btn btn-sm btn-outline-info" title="Ver detalle">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            <i class="bi bi-cash-stack d-block mb-2" style="font-size:2rem;"></i>
                            No hay cuentas por cobrar registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cuentas->hasPages())
        <div class="p-3">{{ $cuentas->links() }}</div>
        @endif
    </div>

</div>
@endsection
