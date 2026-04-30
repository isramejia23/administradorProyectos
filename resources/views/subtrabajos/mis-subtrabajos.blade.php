@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">
            <i class="bi bi-diagram-3-fill title-icon"></i> Mis Subtrabajos
        </h4>
    </div>

    {{-- Filtros --}}
    <form action="{{ route('subtrabajos.mis') }}" method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label mb-1 small">Departamento</label>
                <select name="departamento_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($departamentos as $dep)
                    <option value="{{ $dep->id }}" {{ request('departamento_id') == $dep->id ? 'selected' : '' }}>
                        {{ $dep->nombre_departamento }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1 small">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="proceso"   {{ request('estado') === 'proceso'   ? 'selected' : '' }}>En Proceso</option>
                    <option value="terminado" {{ request('estado') === 'terminado' ? 'selected' : '' }}>Terminado</option>
                    <option value="cancelado" {{ request('estado') === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                       value="{{ request('fecha_inicio') }}">
            </div>
            <div class="col-md-2 d-flex gap-2 align-self-end">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('subtrabajos.mis') }}" class="btn btn-outline-secondary btn-sm w-100" title="Limpiar">
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
                    <th>Proyecto / Cliente</th>
                    <th>Departamento</th>
                    <th>Servicio</th>
                    <th>Estado</th>
                    <th>F. Inicio</th>
                    <th>F. Estimada</th>
                    <th class="text-center" style="width:70px;">Ver</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subtrabajos as $sub)
                <tr>
                    <td class="text-muted small">{{ $sub->id }}</td>
                    <td class="small">
                        <a href="{{ route('proyectos.show', $sub->trabajo_id) }}" class="text-decoration-none text-dark fw-semibold">
                            #{{ $sub->trabajo_id }}
                        </a>
                        <span class="text-muted">— {{ $sub->trabajo->cliente->nombre_completo }}</span>
                    </td>
                    <td class="small">{{ $sub->departamento->nombre_departamento }}</td>
                    <td class="small">{{ $sub->servicio?->nombre_servicio ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $sub->estado_badge }}">{{ $sub->estado_label }}</span>
                    </td>
                    <td class="small">{{ $sub->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                    <td class="small">{{ $sub->fecha_estimada?->format('d/m/Y') ?? '—' }}</td>
                    <td class="text-center">
                        <a href="{{ route('subtrabajos.show', [$sub->trabajo_id, $sub->id]) }}"
                           class="btn btn-sm btn-outline-info" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-diagram-3 d-block mb-1" style="font-size:1.5rem;"></i>
                        No tienes subtrabajos asignados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $subtrabajos->links() }}</div>
</div>
@endsection
