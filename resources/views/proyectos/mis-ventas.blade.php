@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">
            <i class="bi bi-graph-up-arrow title-icon"></i> Mis Ventas
        </h4>
        @can('create', App\Models\Trabajo::class)
        <a href="{{ route('proyectos.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-send me-1"></i> Nueva Solicitud
        </a>
        @endcan
    </div>

    {{-- Filtros --}}
    <form action="{{ route('proyectos.ventas') }}" method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1 small">Cliente</label>
                <input type="text" name="buscar" class="form-control form-control-sm"
                       placeholder="Nombre, apellido o cédula..." value="{{ request('buscar') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1 small">Estado</label>
                <select name="estado_trabajo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="solicitud"  {{ request('estado_trabajo') === 'solicitud'  ? 'selected' : '' }}>Solicitud Pendiente</option>
                    <option value="rechazado"  {{ request('estado_trabajo') === 'rechazado'  ? 'selected' : '' }}>Rechazado</option>
                    <option value="pendiente"  {{ request('estado_trabajo') === 'pendiente'  ? 'selected' : '' }}>Pendiente</option>
                    <option value="proceso"    {{ request('estado_trabajo') === 'proceso'    ? 'selected' : '' }}>En Proceso</option>
                    <option value="terminado"  {{ request('estado_trabajo') === 'terminado'  ? 'selected' : '' }}>Terminado</option>
                    <option value="cancelado"  {{ request('estado_trabajo') === 'cancelado'  ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('proyectos.ventas') }}" class="btn btn-outline-secondary btn-sm w-100" title="Limpiar">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </div>
    </form>

    <div class="bg-white rounded shadow-sm">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Servicio</th>
                    <th>Monto Total</th>
                    <th>% Comisión</th>
                    <th>Comisión</th>
                    <th>Progreso</th>
                    <th>Estado</th>
                    <th>F. Inicio</th>
                    <th>F. Estimada</th>
                    <th class="text-center" style="width:70px;">Ver</th>
                </tr>
            </thead>
            <tbody>
                @forelse($proyectos as $proyecto)
                @php
                    $total      = $proyecto->subtrabajos->count();
                    $terminados = $proyecto->subtrabajos->where('estado', 'terminado')->count();
                    $pct        = $total > 0 ? round($terminados / $total * 100) : 0;
                    $esSolicitud = $proyecto->esSolicitud();
                @endphp
                <tr class="{{ $proyecto->estado_trabajo === 'rechazado' ? 'table-danger' : ($proyecto->estado_trabajo === 'solicitud' ? 'table-warning' : '') }}">
                    <td class="text-muted small">{{ $proyecto->id }}</td>
                    <td class="small">{{ $proyecto->cliente->nombre_completo }}</td>
                    <td class="small">{{ $proyecto->servicio->nombre_servicio }}</td>
                    <td class="small">${{ number_format($proyecto->monto_total, 2) }}</td>
                    <td class="small">
                        @if(! $esSolicitud && $proyecto->porcentaje_comision !== null)
                            <span class="badge bg-secondary">{{ $proyecto->porcentaje_comision }}%</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="small fw-semibold">
                        @if(! $esSolicitud && $proyecto->porcentaje_comision !== null)
                            ${{ number_format($proyecto->monto_comision, 2) }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td style="min-width:120px;">
                        @if($esSolicitud)
                            <span class="text-muted small fst-italic">Sin iniciar</span>
                        @elseif($proyecto->trabajo_unico)
                            <span class="text-muted small">Trabajo único</span>
                        @else
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:8px;">
                                    <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="small text-muted" style="white-space:nowrap;">{{ $terminados }}/{{ $total }}</span>
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $proyecto->estado_badge }}">
                            {{ $proyecto->estado_label }}
                        </span>
                        @if($proyecto->estado_trabajo === 'rechazado' && $proyecto->motivo_rechazo)
                        <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-danger"
                                title="{{ $proyecto->motivo_rechazo }}"
                                data-bs-toggle="tooltip" data-bs-placement="top">
                            <i class="bi bi-info-circle"></i>
                        </button>
                        @endif
                    </td>
                    <td class="small">{{ $proyecto->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                    <td class="small">{{ $proyecto->fecha_estimada?->format('d/m/Y') ?? '—' }}</td>
                    <td class="text-center">
                        <a href="{{ route('proyectos.show', $proyecto->id) }}"
                           class="btn btn-sm btn-outline-info" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                {{-- Fila de motivo de rechazo visible --}}
                @if($proyecto->estado_trabajo === 'rechazado' && $proyecto->motivo_rechazo)
                <tr class="table-danger">
                    <td colspan="11" class="small py-1 ps-4">
                        <i class="bi bi-chat-left-text me-1 text-danger"></i>
                        <span class="text-muted">Motivo del rechazo:</span>
                        {{ $proyecto->motivo_rechazo }}
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="11" class="text-center text-muted py-4">
                        <i class="bi bi-graph-up me-1"></i> No tienes proyectos registrados como vendedor.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $proyectos->links() }}</div>

</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });
</script>
@endpush
