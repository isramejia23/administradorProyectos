@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">
            <i class="bi bi-person-check-fill title-icon"></i> Mis Proyectos
        </h4>
        @can('crear-trabajo')
        <a href="{{ route('proyectos.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Proyecto
        </a>
        @endcan
    </div>

    {{-- Filtros --}}
    <form action="{{ route('proyectos.mis') }}" method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label mb-1 small">Cliente</label>
                <input type="text" name="buscar" class="form-control form-control-sm"
                       placeholder="Nombre o cédula..." value="{{ request('buscar') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small">Servicio</label>
                <input type="text" name="servicio" class="form-control form-control-sm"
                       placeholder="Nombre del servicio..." value="{{ request('servicio') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small">Estado</label>
                <select name="estado_trabajo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="pendiente" {{ request('estado_trabajo') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="proceso"   {{ request('estado_trabajo') === 'proceso'   ? 'selected' : '' }}>En Proceso</option>
                    <option value="terminado" {{ request('estado_trabajo') === 'terminado' ? 'selected' : '' }}>Terminado</option>
                    <option value="cancelado" {{ request('estado_trabajo') === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small">Urgencia</label>
                <select name="nivel_urgencia" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <option value="alto"  {{ request('nivel_urgencia') === 'alto'  ? 'selected' : '' }}>Alto</option>
                    <option value="medio" {{ request('nivel_urgencia') === 'medio' ? 'selected' : '' }}>Medio</option>
                    <option value="bajo"  {{ request('nivel_urgencia') === 'bajo'  ? 'selected' : '' }}>Bajo</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                       value="{{ request('fecha_inicio') }}">
            </div>
            <div class="col-md-1">
                <label class="form-label mb-1 small">Tipo</label>
                <select name="trabajo_unico" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="1" {{ request('trabajo_unico') === '1' ? 'selected' : '' }}>Único</option>
                    <option value="0" {{ request('trabajo_unico') === '0' ? 'selected' : '' }}>Múltiple</option>
                </select>
            </div>
            <div class="col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('proyectos.mis') }}" class="btn btn-outline-secondary btn-sm w-100" title="Limpiar">
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
                    <th>Monto</th>
                    <th>Urgencia</th>
                    <th>Estado</th>
                    <th>F. Inicio</th>
                    <th>F. Estimada</th>
                    <th class="text-center" style="width:90px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($proyectos as $proyecto)
                <tr>
                    <td class="text-muted small">{{ $proyecto->id }}</td>
                    <td class="small">
                        {{ $proyecto->cliente->nombre_completo }}
                        <br><span class="badge bg-secondary fw-normal" style="font-size:.65rem;">{{ $proyecto->cliente->codigo_cliente }}</span>
                    </td>
                    <td class="small">
                        {{ $proyecto->servicio->nombre_servicio }}
                        @if($proyecto->trabajo_unico)
                        <br><span class="text-success" style="font-size:.7rem;"><i class="bi bi-check-circle-fill me-1"></i>Trabajo Único</span>
                        @else
                        <br><span class="text-secondary" style="font-size:.7rem;"><i class="bi bi-x-circle me-1"></i>Múltiples subtrabajos</span>
                        @endif
                    </td>
                    <td class="small">${{ number_format($proyecto->monto_total, 2) }}</td>
                    <td>@include('partials.urgencia-bar', ['nivel' => $proyecto->nivel_urgencia])</td>
                    <td>
                        <span class="badge bg-{{ $proyecto->estado_badge }}">
                            {{ $proyecto->estado_label }}
                        </span>
                    </td>
                    <td class="small">{{ $proyecto->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                    <td class="small">{{ $proyecto->fecha_estimada?->format('d/m/Y') ?? '—' }}</td>
                    <td class="text-center">
                        <a href="{{ route('proyectos.show', $proyecto->id) }}"
                           class="btn btn-sm btn-outline-info" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </a>
                        @can('update', $proyecto)
                        <a href="{{ route('proyectos.edit', $proyecto->id) }}"
                           class="btn btn-sm btn-outline-secondary" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="bi bi-folder2-open me-1"></i> No tienes proyectos asignados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $proyectos->links() }}</div>
</div>
@endsection
