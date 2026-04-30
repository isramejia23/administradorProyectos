@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">
            @if(file_exists(public_path('imagenes/logo-piguma.jpeg')))
                <img src="{{ asset('imagenes/logo-piguma.jpeg') }}" alt="Logo">
            @else
                <i class="bi bi-kanban-fill title-icon"></i>
            @endif
            Proyectos
        </h4>
        @can('create', App\Models\Trabajo::class)
        <a href="{{ route('proyectos.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Proyecto
        </a>
        @endcan
    </div>

    {{-- Filtros --}}
    <form action="{{ route('proyectos.index') }}" method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label mb-1 small">Cliente / Servicio</label>
                <input type="text" name="buscar" class="form-control form-control-sm"
                       placeholder="Nombre, razón social o servicio..." value="{{ request('buscar') }}">
            </div>
            @if(! ($soloDepto ?? false))
            <div class="col-md-2">
                <label class="form-label mb-1 small">Departamento</label>
                <select name="departamento_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($departamentos as $id => $nombre)
                        <option value="{{ $id }}" {{ request('departamento_id') == $id ? 'selected' : '' }}>{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-1">
                <label class="form-label mb-1 small">Estado</label>
                <select name="estado_trabajo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="pendiente"  {{ request('estado_trabajo') === 'pendiente'  ? 'selected' : '' }}>Pendiente</option>
                    <option value="proceso"    {{ request('estado_trabajo') === 'proceso'    ? 'selected' : '' }}>En Proceso</option>
                    <option value="terminado"  {{ request('estado_trabajo') === 'terminado'  ? 'selected' : '' }}>Terminado</option>
                    <option value="cancelado"  {{ request('estado_trabajo') === 'cancelado'  ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label mb-1 small">Urgencia</label>
                <select name="nivel_urgencia" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <option value="alto"  {{ request('nivel_urgencia') === 'alto'  ? 'selected' : '' }}>Alto</option>
                    <option value="medio" {{ request('nivel_urgencia') === 'medio' ? 'selected' : '' }}>Medio</option>
                    <option value="bajo"  {{ request('nivel_urgencia') === 'bajo'  ? 'selected' : '' }}>Bajo</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small">Responsable</label>
                <select name="responsable_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="null" {{ request('responsable_id') === 'null' ? 'selected' : '' }}>Sin responsable</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" {{ request('responsable_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->nombre_completo }}
                        </option>
                    @endforeach
                </select>
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
                <a href="{{ route('proyectos.index') }}" class="btn btn-outline-secondary btn-sm w-100" title="Limpiar">
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
                    <th>Departamento</th>
                    <th>Responsable</th>
                    <th>Monto</th>
                    <th>Urgencia</th>
                    <th>Estado</th>
                    <th>Creado</th>
                    <th class="text-center" style="width:90px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($proyectos as $proyecto)
                <tr>
                    <td class="text-muted small">{{ $proyecto->id }}</td>
                    <td class="small">{{ $proyecto->cliente->nombre_completo }}</td>
                    <td class="small">
                        {{ $proyecto->servicio->nombre_servicio }}
                        @if($proyecto->trabajo_unico)
                        <br><span class="text-success" style="font-size:.7rem;"><i class="bi bi-check-circle-fill me-1"></i>Trabajo Único</span>
                        @else
                        <br><span class="text-secondary" style="font-size:.7rem;"><i class="bi bi-x-circle me-1"></i>Múltiples subtrabajos</span>
                        @endif
                    </td>
                    <td class="small">{{ $proyecto->departamento->nombre_departamento }}</td>
                    <td class="small">{{ $proyecto->responsable?->nombre_completo ?? 'Sin responsable' }}</td>
                    <td>${{ number_format($proyecto->monto_total, 2) }}</td>
                    <td>@include('partials.urgencia-bar', ['nivel' => $proyecto->nivel_urgencia])</td>
                    <td>
                        <span class="badge bg-{{ $proyecto->estado_badge }}">
                            {{ $proyecto->estado_label }}
                        </span>
                    </td>
                    <td class="small">{{ $proyecto->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="d-flex align-items-center justify-content-center gap-1">
                        @canany(['ver-trabajo', 'ver-trabajo-departamento'])
                        <a href="{{ route('proyectos.show', $proyecto->id) }}"
                           class="btn btn-sm btn-outline-info" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </a>
                        @endcanany
                        @can('update', $proyecto)
                        <a href="{{ route('proyectos.edit', $proyecto->id) }}"
                           class="btn btn-sm btn-outline-secondary" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endcan
                        @can('delete', $proyecto)
                        <form action="{{ route('proyectos.destroy', $proyecto->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar"
                                    onclick="confirmDelete(event, this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">No hay proyectos registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $proyectos->links() }}</div>
</div>
@endsection
