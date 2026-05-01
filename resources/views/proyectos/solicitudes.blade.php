@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">
            <i class="bi bi-inbox title-icon"></i> Solicitudes de Proyectos
        </h4>
        @php
            $pendientes = $proyectos->getCollection()->where('estado_trabajo', 'solicitud')->count();
        @endphp
        @if($pendientes > 0)
        <span class="badge bg-warning text-dark fs-6">{{ $pendientes }} pendiente{{ $pendientes !== 1 ? 's' : '' }}</span>
        @endif
    </div>

    {{-- Filtros --}}
    <form action="{{ route('proyectos.solicitudes') }}" method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1 small">Buscar (cliente o vendedor)</label>
                <input type="text" name="buscar" class="form-control form-control-sm"
                       placeholder="Nombre, apellido..." value="{{ request('buscar') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1 small">Estado</label>
                <select name="estado_trabajo" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <option value="solicitud"  {{ request('estado_trabajo') === 'solicitud'  ? 'selected' : '' }}>Pendiente de aprobación</option>
                    <option value="rechazado"  {{ request('estado_trabajo') === 'rechazado'  ? 'selected' : '' }}>Rechazadas</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('proyectos.solicitudes') }}" class="btn btn-outline-secondary btn-sm w-100" title="Limpiar">
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
                    <th>Vendedor</th>
                    <th>Cliente</th>
                    <th>Departamento</th>
                    <th>Servicio</th>
                    <th>Monto Est.</th>
                    <th>Urgencia</th>
                    <th>Estado</th>
                    <th>Enviada</th>
                    <th class="text-center" style="width:100px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($proyectos as $proyecto)
                <tr class="{{ $proyecto->estado_trabajo === 'solicitud' ? 'table-warning' : '' }}">
                    <td class="text-muted small">{{ $proyecto->id }}</td>
                    <td class="small fw-semibold">{{ $proyecto->vendedor?->nombre_completo ?? '—' }}</td>
                    <td class="small">
                        {{ $proyecto->cliente->nombre_completo }}
                        <br><span class="badge bg-secondary fw-normal" style="font-size:.65rem;">{{ $proyecto->cliente->codigo_cliente }}</span>
                    </td>
                    <td class="small">{{ $proyecto->departamento->nombre_departamento }}</td>
                    <td class="small">{{ $proyecto->servicio->nombre_servicio }}</td>
                    <td class="small">${{ number_format($proyecto->monto_total, 2) }}</td>
                    <td>
                        @include('partials.urgencia-bar', ['nivel' => $proyecto->nivel_urgencia])
                    </td>
                    <td>
                        <span class="badge bg-{{ $proyecto->estado_badge }}">{{ $proyecto->estado_label }}</span>
                    </td>
                    <td class="small text-muted">{{ $proyecto->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="{{ route('proyectos.show', $proyecto->id) }}"
                               class="btn btn-sm btn-outline-secondary" title="Ver detalle">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($proyecto->estado_trabajo === 'solicitud')
                            <a href="{{ route('proyectos.aprobar', $proyecto->id) }}"
                               class="btn btn-sm btn-success" title="Revisar y aprobar">
                                <i class="bi bi-check2-circle"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        <i class="bi bi-inbox d-block mb-1" style="font-size:1.5rem;"></i>
                        No hay solicitudes que mostrar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $proyectos->links() }}</div>

</div>
@endsection
